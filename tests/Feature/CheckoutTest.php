<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
    }

    private function validAddressData(array $overrides = []): array
    {
        return array_merge([
            'recipient_name' => 'Jane Doe',
            'phone' => '081234567890',
            'address' => 'Jl. Jenderal Sudirman No. 45, Kebayoran Baru',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
        ], $overrides);
    }

    /**
     * 1. Guest cannot access checkout.
     */
    public function test_guest_cannot_access_checkout(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));

        $postResponse = $this->post(route('checkout.store'), $this->validAddressData());
        $postResponse->assertRedirect(route('login'));
    }

    /**
     * 2. Authenticated customer can access checkout with a non-empty cart.
     */
    public function test_authenticated_customer_can_access_checkout_with_non_empty_cart(): void
    {
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);
        $variant = ProductVariant::where('sku', 'ALX-CORE-MID')->first();
        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('checkout.index'));
        $response->assertStatus(200);
        $response->assertSee('Checkout');
        $response->assertSee('Shipping Information');
        $response->assertSee('Order Summary');
        $response->assertSee($variant->product->name);
        $response->assertSee($variant->color_name);
    }

    /**
     * 3. Empty cart cannot proceed to checkout.
     */
    public function test_empty_cart_redirects_to_cart_page(): void
    {
        $user = User::factory()->create();

        // No cart record
        $response = $this->actingAs($user)->get(route('checkout.index'));
        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error', 'Your cart is empty. Please add items to your cart before proceeding to checkout.');

        // Empty cart record
        Cart::create(['user_id' => $user->id]);
        $response2 = $this->actingAs($user)->get(route('checkout.index'));
        $response2->assertRedirect(route('cart.index'));

        // Post request with empty cart
        $postResponse = $this->actingAs($user)->post(route('checkout.store'), $this->validAddressData());
        $postResponse->assertRedirect(route('cart.index'));
    }

    /**
     * 4. Checkout validates required address fields.
     */
    public function test_checkout_validates_required_address_fields(): void
    {
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);
        $variant = ProductVariant::first();
        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), []);

        $response->assertSessionHasErrors([
            'recipient_name',
            'phone',
            'address',
            'city',
            'province',
            'postal_code',
        ]);
    }

    /**
     * 5, 6, 7, 8, 9, 10, 11, 13, 17. Successful order placement.
     */
    public function test_customer_can_successfully_create_order(): void
    {
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);
        $variant1 = ProductVariant::where('sku', 'ALX-CORE-MID')->first();
        $variant2 = ProductVariant::where('sku', 'ALX-ACT-SNW')->first();

        $initialStock1 = $variant1->stock; // 25
        $initialStock2 = $variant2->stock; // 20

        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant1->id,
            'quantity' => 2,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant2->id,
            'quantity' => 1,
        ]);

        $addressData = $this->validAddressData();

        $response = $this->actingAs($user)->post(route('checkout.store'), $addressData);

        // Check order was created
        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);

        // Redirects to order confirmation
        $response->assertRedirect(route('orders.show', $order));

        // 6. Order number format ALX-YYYYMMDD-XXXXXX
        $this->assertMatchesRegularExpression('/^ALX-\d{8}-[A-Z0-9]{6}$/', $order->order_number);

        // 7. Order status is pending
        $this->assertEquals('pending', $order->order_status);

        // 9, 10. Totals and shipping cost = 0
        $expectedSubtotal = ($variant1->price * 2) + ($variant2->price * 1);
        $this->assertEquals($expectedSubtotal, (float) $order->subtotal);
        $this->assertEquals(0, (float) $order->shipping_cost);
        $this->assertEquals($expectedSubtotal, (float) $order->total);

        // Address association
        $this->assertEquals($user->id, $order->shippingAddress->user_id);
        $this->assertEquals($addressData['recipient_name'], $order->shippingAddress->recipient_name);
        $this->assertEquals($addressData['phone'], $order->shippingAddress->phone);
        $this->assertEquals($addressData['address'], $order->shippingAddress->address);
        $this->assertEquals($addressData['city'], $order->shippingAddress->city);
        $this->assertEquals($addressData['province'], $order->shippingAddress->province);
        $this->assertEquals($addressData['postal_code'], $order->shippingAddress->postal_code);

        // 8. Order items snapshots
        $this->assertCount(2, $order->orderItems);
        $item1 = $order->orderItems->where('product_variant_id', $variant1->id)->first();
        $this->assertEquals($variant1->product->name, $item1->product_name);
        $this->assertEquals($variant1->color_name, $item1->variant_name);
        $this->assertEquals($variant1->sku, $item1->sku);
        $this->assertEquals((float) $variant1->price, (float) $item1->unit_price);
        $this->assertEquals(2, $item1->quantity);
        $this->assertEquals((float) ($variant1->price * 2), (float) $item1->subtotal);

        // 11. Stock deduction
        $variant1->refresh();
        $variant2->refresh();
        $this->assertEquals($initialStock1 - 2, $variant1->stock);
        $this->assertEquals($initialStock2 - 1, $variant2->stock);

        // 13. Cart is cleared
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
        $this->assertEquals(0, $cart->items()->count());

        // 17. Verify no payment or shipment records exist for the newly created order
        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
        $this->assertDatabaseMissing('shipments', ['order_id' => $order->id]);
    }

    /**
     * 12 & 14. Insufficient stock prevents order creation and preserves cart.
     */
    public function test_insufficient_stock_prevents_order_creation_and_preserves_cart(): void
    {
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);
        $variant = ProductVariant::where('sku', 'ALX-CORE-MID')->first();
        $variant->update(['stock' => 2]);

        // Cart quantity exceeds stock
        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 5,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), $this->validAddressData());

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        // Verify no order created
        $this->assertEquals(0, Order::count());
        $this->assertEquals(0, Address::count());

        // Stock unchanged
        $variant->refresh();
        $this->assertEquals(2, $variant->stock);

        // Cart preserved
        $this->assertEquals(1, $cart->items()->count());
        $this->assertEquals(5, $cart->items()->first()->quantity);
    }

    /**
     * Inactive variant prevents order creation and preserves cart.
     */
    public function test_inactive_variant_prevents_order_creation(): void
    {
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);
        $variant = ProductVariant::where('sku', 'ALX-CORE-MID')->first();
        $variant->update(['status' => 'inactive']);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), $this->validAddressData());

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        $this->assertEquals(0, Order::count());
        $this->assertEquals(1, $cart->items()->count());
    }

    /**
     * 15. Customer can view their own order confirmation.
     */
    public function test_customer_can_view_own_order_confirmation(): void
    {
        $user = User::factory()->create();
        $address = Address::create(array_merge($this->validAddressData(), ['user_id' => $user->id]));
        $variant = ProductVariant::first();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ALX-20261003-TEST01',
            'shipping_address_id' => $address->id,
            'subtotal' => 420000,
            'shipping_cost' => 0,
            'total' => 420000,
            'order_status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'variant_name' => $variant->color_name,
            'sku' => $variant->sku,
            'unit_price' => 420000,
            'quantity' => 1,
            'subtotal' => 420000,
        ]);

        $response = $this->actingAs($user)->get(route('orders.show', $order));

        $response->assertStatus(200);
        $response->assertSee('Thank you for your order');
        $response->assertSee('Your order has been placed successfully');
        $response->assertSee('ALX-20261003-TEST01');
        $response->assertSee('Pending');
        $response->assertSee($variant->product->name);
        $response->assertSee($variant->color_name);
        $response->assertSee($address->recipient_name);
        $response->assertSee($address->address);
    }

    /**
     * 16. Customer cannot view another user's order.
     */
    public function test_customer_cannot_view_another_users_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $address = Address::create(array_merge($this->validAddressData(), ['user_id' => $user1->id]));
        $order = Order::create([
            'user_id' => $user1->id,
            'order_number' => 'ALX-20261003-TEST02',
            'shipping_address_id' => $address->id,
            'subtotal' => 420000,
            'shipping_cost' => 0,
            'total' => 420000,
            'order_status' => 'pending',
        ]);

        $response = $this->actingAs($user2)->get(route('orders.show', $order));

        $response->assertStatus(403);
    }
}
