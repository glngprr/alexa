<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected string $serverKey = 'SB-Mid-server-TEST-KEY-123456';
    protected string $clientKey = 'SB-Mid-client-TEST-KEY-654321';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);

        Config::set('services.midtrans.server_key', $this->serverKey);
        Config::set('services.midtrans.client_key', $this->clientKey);
        Config::set('services.midtrans.is_production', false);
        Config::set('services.midtrans.snap_url', 'https://app.sandbox.midtrans.com/snap/v1/transactions');
        Config::set('services.midtrans.api_url', 'https://api.sandbox.midtrans.com/v2');
    }

    private function createTestOrder(User $user, array $overrides = []): Order
    {
        $address = Address::create([
            'user_id' => $user->id,
            'recipient_name' => 'Jane Doe',
            'phone' => '081234567890',
            'address' => 'Jl. Jenderal Sudirman No. 45',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
        ]);

        $variant = ProductVariant::first();

        $order = Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'ALX-20261004-TEST01',
            'shipping_address_id' => $address->id,
            'subtotal' => (int) $variant->price,
            'shipping_cost' => 0,
            'total' => (int) $variant->price,
            'order_status' => 'pending',
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

        return $order;
    }

    private function generateSignature(string $orderId, string $statusCode, string $grossAmount): string
    {
        return hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
    }

    /**
     * 1. Guest cannot initiate payment.
     */
    public function test_guest_cannot_initiate_payment(): void
    {
        $user = User::factory()->create();
        $order = $this->createTestOrder($user);

        $response = $this->post(route('orders.payment', $order->order_number));
        $response->assertRedirect(route('login'));
    }

    /**
     * 2, 5, 6. Customer can initiate payment for their own eligible order.
     */
    public function test_customer_can_initiate_payment_for_own_order(): void
    {
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'mock-snap-token-12345',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-snap-token-12345',
            ], 200),
        ]);

        $user = User::factory()->create();
        $order = $this->createTestOrder($user);

        $response = $this->actingAs($user)->post(route('orders.payment', $order->order_number));

        // Redirects to Midtrans Snap sandbox payment URL
        $response->assertRedirect('https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-snap-token-12345');

        // Payment record created in database
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_gateway' => 'midtrans',
            'amount' => $order->total,
            'status' => 'pending',
            'snap_token' => 'mock-snap-token-12345',
            'snap_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-snap-token-12345',
        ]);

        // Verify request payload sent to Midtrans
        Http::assertSent(function ($request) use ($order) {
            return $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
                && $request['transaction_details']['order_id'] === $order->order_number
                && $request['transaction_details']['gross_amount'] === (int) $order->total;
        });
    }

    /**
     * 3. Customer cannot pay another user's order.
     */
    public function test_customer_cannot_pay_another_users_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = $this->createTestOrder($user1);

        $response = $this->actingAs($user2)->post(route('orders.payment', $order->order_number));
        $response->assertStatus(403);
    }

    /**
     * 4. Cannot initiate payment for an ineligible order.
     */
    public function test_cannot_initiate_payment_for_ineligible_or_already_paid_order(): void
    {
        $user = User::factory()->create();
        $order = $this->createTestOrder($user, ['order_status' => 'processing']);

        Payment::create([
            'order_id' => $order->id,
            'payment_gateway' => 'midtrans',
            'amount' => $order->total,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('orders.payment', $order->order_number));
        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('error');
    }

    /**
     * 7. Repeated payment initiation does not create unintended duplicates.
     */
    public function test_repeated_payment_initiation_reuses_pending_payment(): void
    {
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'mock-snap-token-12345',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-snap-token-12345',
            ], 200),
        ]);

        $user = User::factory()->create();
        $order = $this->createTestOrder($user);

        // First attempt
        $this->actingAs($user)->post(route('orders.payment', $order->order_number));
        $this->assertEquals(1, $order->payments()->count());

        // Second attempt
        $this->actingAs($user)->post(route('orders.payment', $order->order_number));
        $this->assertEquals(1, $order->payments()->count());
    }

    /**
     * 8. Frontend return / viewing order confirmation cannot mark payment as paid without webhook.
     */
    public function test_frontend_return_does_not_mark_order_as_paid(): void
    {
        $user = User::factory()->create();
        $order = $this->createTestOrder($user);

        Payment::create([
            'order_id' => $order->id,
            'payment_gateway' => 'midtrans',
            'amount' => $order->total,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('orders.show', $order->order_number));
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('pending', $order->order_status);
        $this->assertEquals('pending', $order->paymentStatus());
    }

    /**
     * 9. Invalid webhook notification is rejected.
     */
    public function test_invalid_webhook_signature_is_rejected(): void
    {
        $user = User::factory()->create();
        $order = $this->createTestOrder($user);

        $payload = [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => (string) $order->total,
            'signature_key' => 'invalid-fake-signature',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('payments.midtrans.notification'), $payload);
        $response->assertStatus(400);
        $response->assertJson(['status' => 'error', 'message' => 'Invalid signature key.']);

        $order->refresh();
        $this->assertEquals('pending', $order->order_status);
    }

    /**
     * 10, 11. Valid verified successful notification updates payment and order status.
     */
    public function test_valid_settlement_notification_updates_payment_and_order_status(): void
    {
        $user = User::factory()->create();
        $order = $this->createTestOrder($user);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_gateway' => 'midtrans',
            'amount' => $order->total,
            'status' => 'pending',
        ]);

        $statusCode = '200';
        $grossAmount = number_format($order->total, 2, '.', '');
        $signature = $this->generateSignature($order->order_number, $statusCode, $grossAmount);

        $payload = [
            'order_id' => $order->order_number,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-trx-987654',
            'payment_type' => 'bank_transfer',
        ];

        $response = $this->postJson(route('payments.midtrans.notification'), $payload);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $payment->refresh();
        $order->refresh();

        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('bank_transfer', $payment->payment_method);
        $this->assertEquals('midtrans-trx-987654', $payment->transaction_id);
        $this->assertNotNull($payment->paid_at);

        $this->assertEquals('processing', $order->order_status);
    }

    /**
     * 12. Duplicate webhook does not process payment twice (idempotency).
     */
    public function test_duplicate_webhook_is_handled_idempotently(): void
    {
        $user = User::factory()->create();
        $order = $this->createTestOrder($user);

        $statusCode = '200';
        $grossAmount = number_format($order->total, 2, '.', '');
        $signature = $this->generateSignature($order->order_number, $statusCode, $grossAmount);

        $payload = [
            'order_id' => $order->order_number,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-trx-987654',
            'payment_type' => 'gopay',
        ];

        // First notification
        $response1 = $this->postJson(route('payments.midtrans.notification'), $payload);
        $response1->assertStatus(200);

        $payment = $order->payment()->first();
        $originalPaidAt = $payment->paid_at;

        // Second duplicate notification
        $response2 = $this->postJson(route('payments.midtrans.notification'), $payload);
        $response2->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals($originalPaidAt, $payment->paid_at);
        $this->assertEquals(1, $order->payments()->count());
    }

    /**
     * 13. Failed, expired, and cancelled statuses are mapped correctly.
     */
    public function test_failed_expired_and_cancelled_statuses_are_mapped_correctly(): void
    {
        $user = User::factory()->create();

        // Expire
        $orderExpire = $this->createTestOrder($user, ['order_number' => 'ALX-20261004-EXP001']);
        $sigExpire = $this->generateSignature($orderExpire->order_number, '202', (string) $orderExpire->total);
        $this->postJson(route('payments.midtrans.notification'), [
            'order_id' => $orderExpire->order_number,
            'status_code' => '202',
            'gross_amount' => (string) $orderExpire->total,
            'signature_key' => $sigExpire,
            'transaction_status' => 'expire',
        ])->assertStatus(200);

        $orderExpire->refresh();
        $this->assertEquals('expired', $orderExpire->paymentStatus());
        $this->assertEquals('pending', $orderExpire->order_status); // Not marked as processing

        // Cancel
        $orderCancel = $this->createTestOrder($user, ['order_number' => 'ALX-20261004-CNC001']);
        $sigCancel = $this->generateSignature($orderCancel->order_number, '200', (string) $orderCancel->total);
        $this->postJson(route('payments.midtrans.notification'), [
            'order_id' => $orderCancel->order_number,
            'status_code' => '200',
            'gross_amount' => (string) $orderCancel->total,
            'signature_key' => $sigCancel,
            'transaction_status' => 'cancel',
        ])->assertStatus(200);

        $orderCancel->refresh();
        $this->assertEquals('cancelled', $orderCancel->paymentStatus());
        $this->assertEquals('pending', $orderCancel->order_status);

        // Deny / Failed
        $orderDeny = $this->createTestOrder($user, ['order_number' => 'ALX-20261004-DNY001']);
        $sigDeny = $this->generateSignature($orderDeny->order_number, '200', (string) $orderDeny->total);
        $this->postJson(route('payments.midtrans.notification'), [
            'order_id' => $orderDeny->order_number,
            'status_code' => '200',
            'gross_amount' => (string) $orderDeny->total,
            'signature_key' => $sigDeny,
            'transaction_status' => 'deny',
        ])->assertStatus(200);

        $orderDeny->refresh();
        $this->assertEquals('failed', $orderDeny->paymentStatus());
        $this->assertEquals('pending', $orderDeny->order_status);
    }

    /**
     * 14. Server Key is never exposed to frontend views.
     */
    public function test_server_key_is_never_exposed_in_views(): void
    {
        $user = User::factory()->create();
        $order = $this->createTestOrder($user);

        $response = $this->actingAs($user)->get(route('orders.show', $order->order_number));
        $response->assertStatus(200);
        $response->assertDontSee($this->serverKey);
    }

    /**
     * Status refresh endpoint works properly with mocked Midtrans API.
     */
    public function test_customer_can_refresh_payment_status(): void
    {
        $user = User::factory()->create();
        $order = $this->createTestOrder($user);

        Http::fake([
            "https://api.sandbox.midtrans.com/v2/{$order->order_number}/status" => Http::response([
                'order_id' => $order->order_number,
                'status_code' => '200',
                'gross_amount' => (string) $order->total,
                'transaction_status' => 'settlement',
                'transaction_id' => 'midtrans-trx-refresh-111',
                'payment_type' => 'qris',
            ], 200),
        ]);

        $response = $this->actingAs($user)->post(route('orders.payment.status', $order->order_number));
        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('paid', $order->paymentStatus());
        $this->assertEquals('processing', $order->order_status);
    }
}
