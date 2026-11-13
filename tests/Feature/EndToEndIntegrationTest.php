<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EndToEndIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);

        // Configure mock credentials for external services
        Config::set('services.midtrans.server_key', 'midtrans_test_server_key_123');
        Config::set('services.midtrans.client_key', 'midtrans_test_client_key_123');
        Config::set('services.midtrans.is_production', false);
    }

    /**
     * Complete End-to-End Customer Purchase to Fulfillment & Tracking Flow.
     */
    public function test_complete_e2e_purchase_payment_fulfillment_and_tracking_flow(): void
    {
        // -----------------------------------------------------------------
        // 1. Customer Authentication & Catalog Browsing
        // -----------------------------------------------------------------
        $customer = User::factory()->create([
            'name' => 'Sarah Jenkins',
            'email' => 'sarah@example.com',
            'is_admin' => false,
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin Operator',
            'email' => 'admin@alexa.store',
            'is_admin' => true,
        ]);

        // Browse catalog
        $catalogResponse = $this->get(route('products.index'));
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertSee('Alexa Core');

        $variant = ProductVariant::where('status', 'active')->where('stock', '>', 5)->first();
        $this->assertNotNull($variant);
        $initialStock = $variant->stock;
        $unitPrice = $variant->price;

        // View product detail
        $productDetailResponse = $this->get(route('products.show', $variant->product->slug));
        $productDetailResponse->assertStatus(200);
        $productDetailResponse->assertSee($variant->product->name);

        // -----------------------------------------------------------------
        // 2. Add to Cart & View Cart
        // -----------------------------------------------------------------
        $addToCartResponse = $this->actingAs($customer)->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $addToCartResponse->assertRedirect(route('cart.index'));

        $cart = $customer->cart;
        $this->assertNotNull($cart);
        $this->assertEquals(1, $cart->cartItems()->count());
        $this->assertEquals(2, $cart->cartItems()->first()->quantity);

        $cartViewResponse = $this->actingAs($customer)->get(route('cart.index'));
        $cartViewResponse->assertStatus(200);
        $cartViewResponse->assertSee($variant->product->name);

        // -----------------------------------------------------------------
        // 3. Checkout & Order Creation
        // -----------------------------------------------------------------
        $checkoutViewResponse = $this->actingAs($customer)->get(route('checkout.index'));
        $checkoutViewResponse->assertStatus(200);

        $checkoutPayload = [
            'recipient_name' => 'Sarah Jenkins',
            'phone' => '081234567890',
            'address' => 'Jl. Senopati No. 45, Kebayoran Baru',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
        ];

        $checkoutPostResponse = $this->actingAs($customer)->post(route('checkout.store'), $checkoutPayload);

        $order = Order::where('user_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($order);
        $checkoutPostResponse->assertRedirect(route('orders.show', $order->order_number));

        // Verify Cart is emptied and Stock is deducted
        $this->assertEquals(0, $cart->cartItems()->count());
        $variant->refresh();
        $this->assertEquals($initialStock - 2, $variant->stock);

        // Verify Order items snapshot
        $this->assertEquals(1, $order->orderItems()->count());
        $orderItem = $order->orderItems()->first();
        $this->assertEquals($variant->product->name, $orderItem->product_name);
        $this->assertEquals($variant->color_name, $orderItem->variant_name);
        $this->assertEquals($variant->sku, $orderItem->sku);
        $this->assertEquals($unitPrice, $orderItem->unit_price);
        $this->assertEquals(2, $orderItem->quantity);
        $this->assertEquals($unitPrice * 2, $orderItem->subtotal);
        $this->assertEquals($unitPrice * 2, $order->total);
        $this->assertEquals('pending', $order->order_status);

        // -----------------------------------------------------------------
        // 4. Midtrans Sandbox Payment Initiation & Webhook Settlement
        // -----------------------------------------------------------------
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'mock_snap_token_123456',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock_snap_token_123456',
            ], 201),
        ]);

        $paymentInitResponse = $this->actingAs($customer)->post(route('orders.payment', $order->order_number));
        $paymentInitResponse->assertRedirect('https://app.sandbox.midtrans.com/snap/v2/vtweb/mock_snap_token_123456');

        $payment = $order->payments()->first();
        $this->assertNotNull($payment);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('mock_snap_token_123456', $payment->snap_token);

        // Midtrans Webhook Notification
        $serverKey = config('services.midtrans.server_key');
        $statusCode = '200';
        $grossAmount = number_format($order->total, 2, '.', '');
        $signatureKey = hash('sha512', $order->order_number . $statusCode . $grossAmount . $serverKey);

        $webhookPayload = [
            'order_id' => $order->order_number,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signatureKey,
            'transaction_status' => 'settlement',
            'payment_type' => 'credit_card',
            'transaction_id' => 'midtrans-trx-unique-9988',
            'fraud_status' => 'accept',
        ];

        $webhookResponse = $this->postJson(route('payments.midtrans.notification'), $webhookPayload);
        $webhookResponse->assertStatus(200);

        $order->refresh();
        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('processing', $order->order_status);
        $this->assertNotNull($payment->paid_at);

        // Idempotency: replay duplicate webhook
        $replayResponse = $this->postJson(route('payments.midtrans.notification'), $webhookPayload);
        $replayResponse->assertStatus(200);
        $this->assertEquals('paid', $payment->status);

        // -----------------------------------------------------------------
        // 5. Admin Dashboard & Order Review
        // -----------------------------------------------------------------
        $dashboardResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($order->order_number);
        $dashboardResponse->assertSee(number_format($order->total, 0, ',', '.'));

        $adminOrderShowResponse = $this->actingAs($admin)->get(route('admin.orders.show', $order->order_number));
        $adminOrderShowResponse->assertStatus(200);
        $adminOrderShowResponse->assertSee('Sarah Jenkins');
        $adminOrderShowResponse->assertSee('midtrans-trx-unique-9988');

        // -----------------------------------------------------------------
        // 6. Admin Dispatches Shipment
        // -----------------------------------------------------------------
        $dispatchResponse = $this->actingAs($admin)->post(route('admin.orders.shipment.store', $order->order_number), [
            'waybill_id' => 'AWB-JNE-LIVE-778899',
            'courier_code' => 'jne',
            'courier_name' => 'JNE Reguler',
            'courier_service' => 'reg',
        ]);
        $dispatchResponse->assertRedirect(route('orders.show', $order->order_number));

        $order->refresh();
        $shipment = $order->shipment;
        $this->assertNotNull($shipment);
        $this->assertEquals('shipped', $shipment->status);
        $this->assertEquals('AWB-JNE-LIVE-778899', $shipment->waybill_id);
        $this->assertEquals('shipped', $order->order_status);

        // Prevent duplicate shipment creation
        $duplicateDispatch = $this->actingAs($admin)->post(route('admin.orders.shipment.store', $order->order_number));
        $duplicateDispatch->assertSessionHas('error');
        $this->assertEquals(1, $order->shipments()->count());

        // -----------------------------------------------------------------
        // 7. Tracking Status Flow
        // -----------------------------------------------------------------
        $trackResponse = $this->actingAs($customer)->post(route('orders.shipment.track', $order->order_number));
        $trackResponse->assertRedirect(route('orders.show', $order->order_number));

        // Lifecycle updates by admin
        $shipment->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
        $order->update(['order_status' => 'delivered']);

        $shipment->refresh();
        $order->refresh();
        $this->assertEquals('delivered', $shipment->status);
        $this->assertEquals('delivered', $order->order_status);
        $this->assertNotNull($shipment->delivered_at);
        $this->assertEquals('paid', $order->paymentStatus());

        // -----------------------------------------------------------------
        // 8. Historical Order Protection Against Product Deletion
        // -----------------------------------------------------------------
        $product = $variant->product;
        $deleteAttempt = $this->actingAs($admin)->delete(route('admin.products.destroy', $product->slug));
        $deleteAttempt->assertRedirect(route('admin.products.index'));
        $deleteAttempt->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('order_items', ['id' => $orderItem->id]);

        // -----------------------------------------------------------------
        // 9. Authorization & Ownership Protections
        // -----------------------------------------------------------------
        $otherCustomer = User::factory()->create(['is_admin' => false]);

        // Other customer cannot view Sarah's order
        $unauthorizedShow = $this->actingAs($otherCustomer)->get(route('orders.show', $order->order_number));
        $unauthorizedShow->assertStatus(403);

        // Other customer cannot track Sarah's order
        $unauthorizedTrack = $this->actingAs($otherCustomer)->post(route('orders.shipment.track', $order->order_number));
        $unauthorizedTrack->assertStatus(403);

        // Other customer cannot access admin routes
        $unauthorizedAdmin = $this->actingAs($otherCustomer)->get(route('admin.dashboard'));
        $unauthorizedAdmin->assertStatus(403);

        // Guest cannot access checkout or orders
        auth()->logout();
        $guestCheckout = $this->get(route('checkout.index'));
        $guestCheckout->assertRedirect(route('login'));

        $guestAdmin = $this->get(route('admin.dashboard'));
        $guestAdmin->assertRedirect(route('login'));
    }
}
