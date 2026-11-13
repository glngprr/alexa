<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
    }

    private function createPaidOrder(User $user, array $overrides = []): Order
    {
        $address = Address::create([
            'user_id' => $user->id,
            'recipient_name' => 'Jane Doe',
            'phone' => '081234567890',
            'address' => 'Jl. Senopati No. 88, Kebayoran Baru',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
        ]);

        $variant = ProductVariant::first();

        $subtotal = $variant->price * 2;
        $shippingCost = 0;
        $total = $subtotal + $shippingCost;

        $orderData = array_merge([
            'user_id' => $user->id,
            'order_number' => 'ALX-TEST-' . strtoupper(uniqid()),
            'shipping_address_id' => $address->id,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $total,
            'order_status' => 'processing',
        ], $overrides);

        $order = Order::create($orderData);

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'variant_name' => $variant->name,
            'sku' => $variant->sku,
            'unit_price' => $variant->price,
            'quantity' => 2,
            'subtotal' => $subtotal,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_gateway' => 'midtrans',
            'amount' => $total,
            'status' => 'paid',
            'transaction_id' => 'midtrans-test-' . uniqid(),
            'paid_at' => now(),
        ]);

        return $order;
    }

    /**
     * 1. Unauthorized guest or non-admin customer cannot create a shipment.
     */
    public function test_non_admin_cannot_create_shipment(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createPaidOrder($customer);

        // Guest redirect to login
        $guestResponse = $this->post(route('admin.orders.shipment.store', $order->order_number));
        $guestResponse->assertRedirect(route('login'));

        // Customer forbidden (403)
        $customerResponse = $this->actingAs($customer)->post(route('admin.orders.shipment.store', $order->order_number));
        $customerResponse->assertStatus(403);

        $this->assertDatabaseMissing('shipments', ['order_id' => $order->id]);
    }

    /**
     * 2. Cannot create shipment if order is unpaid.
     */
    public function test_cannot_create_shipment_for_unpaid_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer);
        // Set payment status to pending
        $order->payment->update(['status' => 'pending']);

        $response = $this->actingAs($admin)->post(route('admin.orders.shipment.store', $order->order_number));

        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('shipments', ['order_id' => $order->id]);
    }

    /**
     * 3. Admin can create shipment for paid order.
     */
    public function test_admin_can_create_shipment_for_paid_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer);

        $response = $this->actingAs($admin)->post(route('admin.orders.shipment.store', $order->order_number), [
            'waybill_id' => 'AWB_JNE_11223344',
            'courier_code' => 'jne',
            'courier_name' => 'JNE Reguler',
            'courier_service' => 'reg',
        ]);

        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shipments', [
            'order_id' => $order->id,
            'waybill_id' => 'AWB_JNE_11223344',
            'courier_code' => 'jne',
            'courier_service' => 'reg',
            'courier_name' => 'JNE Reguler',
            'status' => 'shipped',
        ]);

        $order->refresh();
        $this->assertEquals('shipped', $order->order_status);
        $this->assertEquals('paid', $order->paymentStatus());
    }

    /**
     * 4. Duplicate shipment creation is prevented.
     */
    public function test_duplicate_shipment_creation_is_prevented(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer);

        Shipment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'waybill_id' => 'AWB_EXISTING_111',
            'courier_code' => 'jne',
            'courier_service' => 'reg',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.shipment.store', $order->order_number));

        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('error');

        $this->assertEquals(1, $order->shipments()->count());
    }

    /**
     * 5. Customer can view their own shipment tracking, other customer cannot.
     */
    public function test_customer_can_view_own_shipment_details(): void
    {
        $customer1 = User::factory()->create();
        $customer2 = User::factory()->create();

        $order = $this->createPaidOrder($customer1);

        Shipment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'waybill_id' => 'AWB-TEST-889900',
            'courier_code' => 'jne',
            'courier_service' => 'reg',
            'courier_name' => 'JNE Regular',
            'status' => 'in_transit',
            'history' => [
                [
                    'note' => 'Package picked up by courier',
                    'updated_at' => '2026-10-04 09:00:00',
                ],
            ],
        ]);

        // Owner can view
        $response = $this->actingAs($customer1)->get(route('orders.show', $order->order_number));
        $response->assertStatus(200);
        $response->assertSee('Shipping &amp; Tracking', false);
        $response->assertSee('AWB-TEST-889900');
        $response->assertSee('JNE Regular');
        $response->assertSee('Package picked up by courier');

        // Other customer cannot view
        $response2 = $this->actingAs($customer2)->get(route('orders.show', $order->order_number));
        $response2->assertStatus(403);
    }

    /**
     * 6. Tracking refresh informs customer of up to date status.
     */
    public function test_customer_can_refresh_tracking(): void
    {
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer);

        Shipment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'waybill_id' => 'AWB-LIVE-123',
            'status' => 'shipped',
        ]);

        $response = $this->actingAs($customer)->post(route('orders.shipment.track', $order->order_number));

        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('info');
    }

    /**
     * 7. Tracking refresh without shipment returns error.
     */
    public function test_refresh_tracking_without_shipment_returns_error(): void
    {
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer);

        $response = $this->actingAs($customer)->post(route('orders.shipment.track', $order->order_number));

        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('error');
    }
}
