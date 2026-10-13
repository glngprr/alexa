<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Collection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
    }

    private function createAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function createCustomer(): User
    {
        return User::factory()->create(['is_admin' => false]);
    }

    private function createSampleOrder(User $customer, array $overrides = []): Order
    {
        $address = Address::create([
            'user_id' => $customer->id,
            'recipient_name' => 'Jane Recipient',
            'phone' => '081234567890',
            'address' => 'Jl. Sudirman No. 10',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
        ]);

        $variant = ProductVariant::first();

        $order = Order::create(array_merge([
            'user_id' => $customer->id,
            'order_number' => 'ALX-TEST-ORD01',
            'shipping_address_id' => $address->id,
            'subtotal' => (int) $variant->price,
            'shipping_cost' => 0,
            'total' => (int) $variant->price,
            'order_status' => 'processing',
        ], $overrides));

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'variant_name' => $variant->color_name,
            'sku' => $variant->sku,
            'unit_price' => $variant->price,
            'quantity' => 1,
            'subtotal' => $variant->price,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_gateway' => 'midtrans',
            'payment_method' => 'bank_transfer',
            'transaction_id' => 'trx-sample-12345',
            'amount' => $order->total,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $order;
    }

    /**
     * 1. Guest cannot access admin routes (redirected to login).
     */
    public function test_guest_cannot_access_admin_dashboard_or_routes(): void
    {
        $responseDashboard = $this->get(route('admin.dashboard'));
        $responseDashboard->assertRedirect(route('login'));

        $responseProducts = $this->get(route('admin.products.index'));
        $responseProducts->assertRedirect(route('login'));

        $responseOrders = $this->get(route('admin.orders.index'));
        $responseOrders->assertRedirect(route('login'));
    }

    /**
     * 2. Customer cannot access admin routes (aborts with 403 Forbidden).
     */
    public function test_customer_cannot_access_admin_routes(): void
    {
        $customer = $this->createCustomer();

        $responseDashboard = $this->actingAs($customer)->get(route('admin.dashboard'));
        $responseDashboard->assertStatus(403);

        $responseProducts = $this->actingAs($customer)->get(route('admin.products.index'));
        $responseProducts->assertStatus(403);

        $responseOrders = $this->actingAs($customer)->get(route('admin.orders.index'));
        $responseOrders->assertStatus(403);
    }

    /**
     * 3. Admin can access admin dashboard.
     */
    public function test_admin_can_access_dashboard(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Executive Dashboard');
        $response->assertSee('Total Paid Revenue');
    }

    /**
     * 4 & 5. Dashboard displays actual data and revenue strictly counts paid payments only.
     */
    public function test_dashboard_displays_actual_data_and_only_paid_revenue(): void
    {
        $admin = $this->createAdmin();
        $customer1 = $this->createCustomer();
        $customer2 = $this->createCustomer();

        // 1 Paid order with 899,000
        $orderPaid = $this->createSampleOrder($customer1, ['order_number' => 'ALX-PAID-01', 'total' => 899000]);

        $address = Address::create([
            'user_id' => $customer2->id,
            'recipient_name' => 'John Customer',
            'phone' => '081299998888',
            'address' => 'Jl. Thamrin No. 5',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'postal_code' => '10350',
        ]);

        // 1 Unpaid/Pending order with 1,200,000
        $orderPending = Order::create([
            'user_id' => $customer2->id,
            'order_number' => 'ALX-PENDING-02',
            'shipping_address_id' => $address->id,
            'subtotal' => 1200000,
            'shipping_cost' => 0,
            'total' => 1200000,
            'order_status' => 'pending',
        ]);
        Payment::create([
            'order_id' => $orderPending->id,
            'payment_gateway' => 'midtrans',
            'amount' => 1200000,
            'status' => 'pending',
        ]);

        // 1 Failed order with 500,000
        $orderFailed = Order::create([
            'user_id' => $customer2->id,
            'order_number' => 'ALX-FAILED-03',
            'shipping_address_id' => $address->id,
            'subtotal' => 500000,
            'shipping_cost' => 0,
            'total' => 500000,
            'order_status' => 'cancelled',
        ]);
        Payment::create([
            'order_id' => $orderFailed->id,
            'payment_gateway' => 'midtrans',
            'amount' => 500000,
            'status' => 'failed',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Revenue must ONLY be 899.000, not including 1.200.000 or 500.000
        $response->assertSee('Rp 899.000');
        $response->assertDontSee('Rp 2.599.000');
        $response->assertDontSee('Rp 2.099.000');

        // Total orders is 3
        $response->assertSee('ALX-PAID-01');
    }

    /**
     * 6. Admin can CRUD products and variants.
     */
    public function test_admin_can_crud_product_and_variants(): void
    {
        $admin = $this->createAdmin();
        $collection = Collection::first();

        // 1. Create Product
        $createResponse = $this->actingAs($admin)->post(route('admin.products.store'), [
            'collection_id' => $collection->id,
            'name' => 'Alexa Aeris Tumbler',
            'slug' => 'alexa-aeris-tumbler',
            'description' => 'Lightweight insulated aeris bottle.',
            'capacity' => '600 ml',
            'material' => '18/8 Stainless Steel',
            'lid_type' => 'Aero Sip Cap',
            'weight' => '±290g',
            'status' => 'active',
            'variant_name' => 'Aeris Pearl',
            'color_name' => 'Pearl White',
            'color_hex' => '#FFFFFF',
            'sku' => 'ALX-AER-PRL',
            'price' => 750000,
            'stock' => 15,
            'image' => 'images/products/core-ivory.png',
        ]);

        $createResponse->assertRedirect(route('admin.products.index'));
        $createResponse->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Alexa Aeris Tumbler',
            'slug' => 'alexa-aeris-tumbler',
        ]);
        $this->assertDatabaseHas('product_variants', [
            'sku' => 'ALX-AER-PRL',
            'price' => 750000,
            'stock' => 15,
        ]);

        $product = Product::where('slug', 'alexa-aeris-tumbler')->first();

        // 2. Add another variant
        $addVariantResponse = $this->actingAs($admin)->post(route('admin.products.variants.store', $product->slug), [
            'name' => 'Aeris Onyx',
            'color_name' => 'Matte Onyx',
            'color_hex' => '#111111',
            'sku' => 'ALX-AER-ONX',
            'price' => 750000,
            'stock' => 25,
            'status' => 'active',
            'image' => 'images/products/core-midnight.png',
        ]);

        $addVariantResponse->assertRedirect(route('admin.products.edit', $product->slug));
        $this->assertDatabaseHas('product_variants', [
            'sku' => 'ALX-AER-ONX',
            'stock' => 25,
        ]);

        // 3. Edit product
        $updateResponse = $this->actingAs($admin)->put(route('admin.products.update', $product->slug), [
            'collection_id' => $collection->id,
            'name' => 'Alexa Aeris Pro Tumbler',
            'slug' => 'alexa-aeris-pro-tumbler',
            'description' => 'Updated description.',
            'capacity' => '650 ml',
            'material' => '18/8 Stainless Steel',
            'lid_type' => 'Aero Sip Cap',
            'weight' => '±295g',
            'status' => 'active',
        ]);

        $updateResponse->assertRedirect(route('admin.products.edit', 'alexa-aeris-pro-tumbler'));
        $this->assertDatabaseHas('products', [
            'name' => 'Alexa Aeris Pro Tumbler',
            'slug' => 'alexa-aeris-pro-tumbler',
        ]);

        $product->refresh();

        // 4. Update variant
        $variant = ProductVariant::where('sku', 'ALX-AER-ONX')->first();
        $updateVariantResponse = $this->actingAs($admin)->put(route('admin.variants.update', $variant->id), [
            'name' => 'Aeris Onyx Stealth',
            'color_name' => 'Stealth Onyx',
            'color_hex' => '#0A0A0A',
            'sku' => 'ALX-AER-ONX',
            'price' => 790000,
            'stock' => 30,
            'status' => 'active',
        ]);
        $updateVariantResponse->assertRedirect(route('admin.products.edit', $product->slug));
        $this->assertDatabaseHas('product_variants', [
            'sku' => 'ALX-AER-ONX',
            'price' => 790000,
            'stock' => 30,
        ]);

        // 5. Delete variant
        $deleteVariantResponse = $this->actingAs($admin)->delete(route('admin.variants.destroy', $variant->id));
        $deleteVariantResponse->assertRedirect(route('admin.products.edit', $product->slug));
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);

        // 6. Delete product
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.products.destroy', $product->slug));
        $deleteResponse->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * 7. Validation for product price and stock (negative stock or price rejected).
     */
    public function test_price_and_stock_validation(): void
    {
        $admin = $this->createAdmin();
        $collection = Collection::first();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'collection_id' => $collection->id,
            'name' => 'Invalid Product',
            'description' => 'Test',
            'capacity' => '500 ml',
            'material' => 'Steel',
            'lid_type' => 'Cap',
            'weight' => '300g',
            'status' => 'active',
            'variant_name' => 'Invalid Var',
            'color_name' => 'Invalid',
            'sku' => 'INV-001',
            'price' => -50000, // Invalid negative price
            'stock' => -5,     // Invalid negative stock
        ]);

        $response->assertSessionHasErrors(['price', 'stock']);
    }

    /**
     * 8. Admin can view order list and detail.
     */
    public function test_admin_can_view_order_list_and_details(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $order = $this->createSampleOrder($customer);

        // List
        $listResponse = $this->actingAs($admin)->get(route('admin.orders.index'));
        $listResponse->assertStatus(200);
        $listResponse->assertSee($order->order_number);
        $listResponse->assertSee($customer->name);

        // Show
        $showResponse = $this->actingAs($admin)->get(route('admin.orders.show', $order->order_number));
        $showResponse->assertStatus(200);
        $showResponse->assertSee($order->order_number);
        $showResponse->assertSee('Purchased Items');
        $showResponse->assertSee('Midtrans Snap Sandbox');
    }

    /**
     * 9. Admin cannot manipulate payment status to paid via order status update.
     */
    public function test_admin_cannot_manipulate_payment_status(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        $address = Address::create([
            'user_id' => $customer->id,
            'recipient_name' => 'Unpaid Customer',
            'phone' => '081211112222',
            'address' => 'Jl. Kebon Jeruk No. 12',
            'city' => 'Jakarta Barat',
            'province' => 'DKI Jakarta',
            'postal_code' => '11530',
        ]);

        // Create unpaid order
        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ALX-UNPAID-999',
            'shipping_address_id' => $address->id,
            'subtotal' => 899000,
            'shipping_cost' => 0,
            'total' => 899000,
            'order_status' => 'pending',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_gateway' => 'midtrans',
            'amount' => 899000,
            'status' => 'pending',
        ]);

        // Attempting to change unpaid order directly to delivered should be blocked
        $response = $this->actingAs($admin)->patch(route('admin.orders.status.update', $order->order_number), [
            'order_status' => 'delivered',
        ]);
        $response->assertSessionHas('error');

        $order->refresh();
        $payment->refresh();

        $this->assertEquals('pending', $order->order_status);
        $this->assertEquals('pending', $payment->status); // Payment status remains pending
    }

    /**
     * 10. Duplicate shipment cannot be created from admin.
     */
    public function test_admin_cannot_create_duplicate_shipment(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $order = $this->createSampleOrder($customer);

        // Existing shipment
        Shipment::create([
            'order_id' => $order->id,
            'provider' => 'biteship',
            'biteship_order_id' => 'bs_existing_123',
            'waybill_id' => 'AWB-123',
            'status' => 'shipped',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.shipment.store', $order->order_number));
        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('error');

        $this->assertEquals(1, $order->shipments()->count());
    }

    /**
     * 11. Historical order data is protected against product deletion.
     */
    public function test_product_linked_to_existing_orders_cannot_be_deleted(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $order = $this->createSampleOrder($customer);

        $variant = $order->orderItems->first()->productVariant;
        $product = $variant->product;

        $response = $this->actingAs($admin)->delete(route('admin.products.destroy', $product->slug));
        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('error');

        // Product and order records remain intact
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('order_items', ['id' => $order->orderItems->first()->id]);
    }
}
