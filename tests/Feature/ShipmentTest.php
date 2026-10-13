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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShipmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);

        Config::set('services.biteship.api_key', 'biteship_test_mock_key_123456');
        Config::set('services.biteship.base_url', 'https://api.biteship.com');
        Config::set('services.biteship.courier_company', 'jne');
        Config::set('services.biteship.courier_type', 'reg');
        Config::set('services.biteship.origin', [
            'contact_name' => 'Alexa Fulfillment Center',
            'contact_phone' => '081299887766',
            'contact_email' => 'fulfillment@alexa.store',
            'organization' => 'Alexa Lifestyle Indonesia',
            'address' => 'Jl. Jenderal Sudirman Kav. 52-53, SCBD',
            'postal_code' => 12190,
            'note' => 'Alexa Warehouse Gate 3',
        ]);
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

        $order = Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'ALX-20261004-SHIP01',
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
            'transaction_id' => 'trx-paid-12345',
            'amount' => $order->total,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $order;
    }

    /**
     * 1. Guest & Regular Customer cannot create shipment.
     */
    public function test_non_admin_cannot_create_shipment(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createPaidOrder($customer);

        // Guest
        $guestResponse = $this->post(route('admin.orders.shipment.store', $order->order_number));
        $guestResponse->assertRedirect(route('login'));

        // Authenticated customer
        $customerResponse = $this->actingAs($customer)->post(route('admin.orders.shipment.store', $order->order_number));
        $customerResponse->assertStatus(403);
    }

    /**
     * 2. Admin cannot create shipment for unpaid order.
     */
    public function test_admin_cannot_create_shipment_for_unpaid_order(): void
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
     * 3. Admin can create shipment for paid order via Biteship Sandbox.
     * Newly allocated shipment is mapped to internal 'pending' and order remains 'processing'.
     */
    public function test_admin_can_create_shipment_for_paid_order(): void
    {
        Http::fake([
            'https://api.biteship.com/v1/orders' => Http::response([
                'success' => true,
                'message' => 'Order successfully created',
                'object' => 'order',
                'id' => 'biteship_order_60a1b2c3d4e5f6',
                'courier' => [
                    'tracking_id' => 'TRACK_BS_987654321',
                    'waybill_id' => 'AWB_JNE_11223344',
                    'company' => 'jne',
                    'name' => 'JNE Reguler',
                    'type' => 'reg',
                    'link' => 'https://tracking.biteship.com/TRACK_BS_987654321',
                ],
                'status' => 'allocated',
                'price' => 10000,
            ], 200),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer);

        $response = $this->actingAs($admin)->post(route('admin.orders.shipment.store', $order->order_number));

        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('success');

        // Allocated shipment is pending
        $this->assertDatabaseHas('shipments', [
            'order_id' => $order->id,
            'provider' => 'biteship',
            'biteship_order_id' => 'biteship_order_60a1b2c3d4e5f6',
            'biteship_tracking_id' => 'TRACK_BS_987654321',
            'waybill_id' => 'AWB_JNE_11223344',
            'courier_code' => 'jne',
            'courier_service' => 'reg',
            'courier_name' => 'JNE Reguler',
            'status' => 'pending',
            'tracking_url' => 'https://tracking.biteship.com/TRACK_BS_987654321',
        ]);

        $order->refresh();

        // Order status remains processing initially after shipment allocation
        $this->assertEquals('processing', $order->order_status);

        // Payment status remains paid
        $this->assertEquals('paid', $order->paymentStatus());

        // Verify origin address and delivery date/time format sent to Biteship
        Http::assertSent(function ($request) use ($order) {
            $validDateFormat = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($request['delivery_date'] ?? ''));
            $validTimeFormat = (bool) preg_match('/^\d{2}:\d{2}$/', (string) ($request['delivery_time'] ?? ''));

            return $request->url() === 'https://api.biteship.com/v1/orders'
                && $request['shipper_contact_name'] === 'Alexa Fulfillment Center'
                && $request['origin_address'] === 'Jl. Jenderal Sudirman Kav. 52-53, SCBD'
                && $request['destination_contact_name'] === $order->shippingAddress->recipient_name
                && $request['delivery_type'] === 'later'
                && $validDateFormat
                && $validTimeFormat;
        });
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
            'provider' => 'biteship',
            'biteship_order_id' => 'biteship_existing_111',
            'biteship_tracking_id' => 'track_existing_111',
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
     * 5. External API error is handled without modifying payment or order status.
     */
    public function test_biteship_api_error_is_handled_gracefully(): void
    {
        Http::fake([
            'https://api.biteship.com/v1/orders' => Http::response([
                'success' => false,
                'error' => 'Courier service temporarily unavailable',
            ], 503),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer);

        $response = $this->actingAs($admin)->post(route('admin.orders.shipment.store', $order->order_number));

        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('shipments', ['order_id' => $order->id]);
        $order->refresh();
        $this->assertEquals('processing', $order->order_status);
        $this->assertEquals('paid', $order->paymentStatus());
    }

    /**
     * 6. Customer can view their own shipment tracking, other customer cannot.
     */
    public function test_customer_can_view_own_shipment_details(): void
    {
        $customer1 = User::factory()->create();
        $customer2 = User::factory()->create();

        $order = $this->createPaidOrder($customer1);

        Shipment::create([
            'order_id' => $order->id,
            'provider' => 'biteship',
            'biteship_order_id' => 'biteship_order_view_1',
            'biteship_tracking_id' => 'TRACK_VIEW_123',
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
     * 7. Status 'placed', 'allocated', and 'picking_up' remain 'pending' and order remains 'processing'.
     */
    public function test_status_placed_allocated_picking_up_remain_pending(): void
    {
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'provider' => 'biteship',
            'biteship_order_id' => 'biteship_order_placed_123',
            'biteship_tracking_id' => null,
            'waybill_id' => null,
            'status' => 'pending',
        ]);

        Http::fake([
            'https://api.biteship.com/v1/orders/biteship_order_placed_123' => Http::response([
                'success' => true,
                'id' => 'biteship_order_placed_123',
                'status' => 'placed',
                'courier' => [
                    'tracking_id' => null,
                    'waybill_id' => null,
                    'company' => 'jne',
                    'type' => 'reg',
                    'history' => [],
                    'link' => null,
                ],
            ], 200),
        ]);

        $response = $this->actingAs($customer)->post(route('orders.shipment.track', $order->order_number));

        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('info');

        $shipment->refresh();
        $order->refresh();

        $this->assertEquals('pending', $shipment->status);
        $this->assertEquals('processing', $order->order_status);
        $this->assertNull($shipment->shipped_at);
        $this->assertEquals('paid', $order->paymentStatus());
    }

    /**
     * 8. Status 'picked' maps to internal 'shipped' and updates order status to 'shipped'.
     */
    public function test_status_picked_maps_to_shipped_and_updates_order_status(): void
    {
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'provider' => 'biteship',
            'biteship_order_id' => 'biteship_order_picked_123',
            'biteship_tracking_id' => 'TRACK_PICKED_123',
            'waybill_id' => 'AWB-JNE-PICKED',
            'status' => 'pending',
        ]);

        Http::fake([
            'https://api.biteship.com/v1/trackings/TRACK_PICKED_123' => Http::response([
                'success' => true,
                'tracking_id' => 'TRACK_PICKED_123',
                'waybill_id' => 'AWB-JNE-PICKED',
                'status' => 'picked',
                'courier' => [
                    'company' => 'jne',
                    'name' => 'JNE Reguler',
                ],
                'history' => [
                    [
                        'note' => 'Package has been picked up by courier',
                        'updated_at' => '2026-10-04 10:00:00',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($customer)->post(route('orders.shipment.track', $order->order_number));
        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('success');

        $shipment->refresh();
        $order->refresh();

        $this->assertEquals('shipped', $shipment->status);
        $this->assertEquals('shipped', $order->order_status);
        $this->assertNotNull($shipment->shipped_at);
        $this->assertEquals('paid', $order->paymentStatus());
    }

    /**
     * 9. Status 'in_transit' and 'dropping_off' map to internal 'in_transit' and update order status.
     */
    public function test_status_in_transit_updates_shipment_and_order(): void
    {
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'provider' => 'biteship',
            'biteship_order_id' => 'biteship_order_transit_1',
            'biteship_tracking_id' => 'TRACK_TRANSIT_999',
            'waybill_id' => 'AWB-PENDING',
            'courier_code' => 'jne',
            'courier_service' => 'reg',
            'status' => 'pending',
        ]);

        Http::fake([
            'https://api.biteship.com/v1/trackings/TRACK_TRANSIT_999' => Http::response([
                'success' => true,
                'tracking_id' => 'TRACK_TRANSIT_999',
                'waybill_id' => 'AWB-JNE-LIVE-999',
                'courier' => [
                    'company' => 'jne',
                    'name' => 'JNE Reguler',
                ],
                'status' => 'in_transit',
                'history' => [
                    [
                        'note' => 'On the way to destination delivery hub',
                        'updated_at' => '2026-10-04 11:30:00',
                    ],
                ],
                'link' => 'https://tracking.biteship.com/TRACK_TRANSIT_999',
            ], 200),
        ]);

        $response = $this->actingAs($customer)->post(route('orders.shipment.track', $order->order_number));
        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('success');

        $shipment->refresh();
        $order->refresh();

        $this->assertEquals('in_transit', $shipment->status);
        $this->assertEquals('AWB-JNE-LIVE-999', $shipment->waybill_id);
        $this->assertEquals('shipped', $order->order_status);
        $this->assertNotNull($shipment->shipped_at);
        $this->assertEquals('paid', $order->paymentStatus());
    }

    /**
     * 10. Status 'delivered' updates shipment to 'delivered', order to 'delivered', sets delivered_at.
     */
    public function test_status_delivered_updates_shipment_and_order_status(): void
    {
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer, ['order_status' => 'shipped']);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'provider' => 'biteship',
            'biteship_tracking_id' => 'TRACK_DELIVERED_777',
            'waybill_id' => 'AWB-777',
            'status' => 'in_transit',
            'shipped_at' => now()->subDay(),
        ]);

        Http::fake([
            'https://api.biteship.com/v1/trackings/TRACK_DELIVERED_777' => Http::response([
                'success' => true,
                'waybill_id' => 'AWB-777',
                'status' => 'delivered',
                'history' => [
                    [
                        'note' => 'Delivered to recipient (Jane Doe)',
                        'updated_at' => '2026-10-04 14:00:00',
                    ],
                ],
            ], 200),
        ]);

        $this->actingAs($customer)->post(route('orders.shipment.track', $order->order_number));

        $shipment->refresh();
        $order->refresh();

        $this->assertEquals('delivered', $shipment->status);
        $this->assertEquals('delivered', $order->order_status);
        $this->assertNotNull($shipment->delivered_at);
        $this->assertEquals('paid', $order->paymentStatus());
    }

    /**
     * 11. Status 'delivered' does not regress if out-of-order tracking returns 'in_transit' or 'picked'.
     */
    public function test_delivered_status_does_not_regress(): void
    {
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer, ['order_status' => 'delivered']);

        $deliveredAtTime = now()->subHours(2);
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'provider' => 'biteship',
            'biteship_tracking_id' => 'TRACK_DELIVERED_NONREG',
            'waybill_id' => 'AWB-NONREG',
            'status' => 'delivered',
            'shipped_at' => now()->subDay(),
            'delivered_at' => $deliveredAtTime,
        ]);

        // Mock out-of-order tracking payload returning 'in_transit'
        Http::fake([
            'https://api.biteship.com/v1/trackings/TRACK_DELIVERED_NONREG' => Http::response([
                'success' => true,
                'waybill_id' => 'AWB-NONREG',
                'status' => 'in_transit',
                'history' => [
                    [
                        'note' => 'Out-of-order event received',
                        'updated_at' => '2026-10-04 11:00:00',
                    ],
                ],
            ], 200),
        ]);

        $this->actingAs($customer)->post(route('orders.shipment.track', $order->order_number));

        $shipment->refresh();
        $order->refresh();

        // Must remain delivered
        $this->assertEquals('delivered', $shipment->status);
        $this->assertEquals('delivered', $order->order_status);
        $this->assertEquals($deliveredAtTime->toDateTimeString(), $shipment->delivered_at->toDateTimeString());
        $this->assertEquals('paid', $order->paymentStatus());
    }

    /**
     * 12. Unknown external status safely preserves existing internal status.
     */
    public function test_unknown_external_status_preserves_current_shipment_status(): void
    {
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer, ['order_status' => 'shipped']);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'provider' => 'biteship',
            'biteship_tracking_id' => 'TRACK_UNKNOWN_123',
            'waybill_id' => 'AWB-UNKNOWN-123',
            'status' => 'in_transit',
            'shipped_at' => now()->subDay(),
        ]);

        Http::fake([
            'https://api.biteship.com/v1/trackings/TRACK_UNKNOWN_123' => Http::response([
                'success' => true,
                'waybill_id' => 'AWB-UNKNOWN-123',
                'status' => 'custom_unknown_vendor_event',
                'history' => [],
            ], 200),
        ]);

        $this->actingAs($customer)->post(route('orders.shipment.track', $order->order_number));

        $shipment->refresh();
        $order->refresh();

        $this->assertEquals('in_transit', $shipment->status);
        $this->assertEquals('shipped', $order->order_status);
        $this->assertEquals('paid', $order->paymentStatus());
    }

    /**
     * 13. API failure does not corrupt previous shipment status or history.
     */
    public function test_api_failure_does_not_corrupt_previous_status(): void
    {
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer, ['order_status' => 'shipped']);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'provider' => 'biteship',
            'biteship_tracking_id' => 'TRACK_FAIL_123',
            'waybill_id' => 'AWB-FAIL-123',
            'status' => 'in_transit',
            'history' => [
                [
                    'note' => 'Previous valid event',
                    'updated_at' => '2026-10-04 10:00:00',
                ],
            ],
        ]);

        Http::fake([
            'https://api.biteship.com/v1/trackings/TRACK_FAIL_123' => Http::response([
                'success' => false,
                'error' => 'Rate limit exceeded',
            ], 429),
        ]);

        $response = $this->actingAs($customer)->post(route('orders.shipment.track', $order->order_number));
        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('error');

        $shipment->refresh();
        $order->refresh();

        $this->assertEquals('in_transit', $shipment->status);
        $this->assertEquals('shipped', $order->order_status);
        $this->assertCount(1, $shipment->history);
        $this->assertEquals('Previous valid event', $shipment->history[0]['note']);
        $this->assertEquals('paid', $order->paymentStatus());
    }

    /**
     * 14. Refresh tracking updates AWB and tracking ID when assigned in Order API response.
     */
    public function test_refresh_tracking_updates_awb_when_assigned_in_order_api(): void
    {
        $customer = User::factory()->create();
        $order = $this->createPaidOrder($customer);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'provider' => 'biteship',
            'biteship_order_id' => 'biteship_order_assigned_456',
            'biteship_tracking_id' => null,
            'waybill_id' => null,
            'status' => 'pending',
        ]);

        Http::fake([
            'https://api.biteship.com/v1/orders/biteship_order_assigned_456' => Http::response([
                'success' => true,
                'id' => 'biteship_order_assigned_456',
                'status' => 'allocated',
                'courier' => [
                    'tracking_id' => 'TRACK_NEW_456',
                    'waybill_id' => 'AWB-NEW-JNE-456',
                    'company' => 'jne',
                    'name' => 'JNE Reguler',
                    'type' => 'reg',
                    'history' => [],
                    'link' => 'https://tracking.biteship.com/TRACK_NEW_456',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($customer)->post(route('orders.shipment.track', $order->order_number));

        $response->assertRedirect(route('orders.show', $order->order_number));
        $response->assertSessionHas('success');

        $shipment->refresh();
        $this->assertEquals('TRACK_NEW_456', $shipment->biteship_tracking_id);
        $this->assertEquals('AWB-NEW-JNE-456', $shipment->waybill_id);
        $this->assertEquals('pending', $shipment->status);
    }
}
