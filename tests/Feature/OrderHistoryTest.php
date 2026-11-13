<?php

namespace Tests\Feature;

use App\Models\Address;
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

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
    }

    /**
     * Helper to create an order for a given user.
     */
    private function createOrderForUser(User $user, array $overrides = []): Order
    {
        $address = Address::create([
            'user_id' => $user->id,
            'recipient_name' => $user->name,
            'phone' => '08123456789',
            'address' => 'Jl. Jenderal Sudirman No. 10',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
        ]);

        $orderNumber = $overrides['order_number'] ?? ('ALX-TEST-' . uniqid());
        $subtotal = $overrides['subtotal'] ?? 899000;
        $total = $overrides['total'] ?? $subtotal;

        $order = Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => $orderNumber,
            'shipping_address_id' => $address->id,
            'subtotal' => $subtotal,
            'shipping_cost' => 0,
            'total' => $total,
            'order_status' => 'pending',
        ], $overrides));

        $variant = ProductVariant::where('sku', 'ALX-FRM-MID')->first() ?? ProductVariant::first();

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

    /**
     * 1. Guest cannot access My Orders page and is redirected to login.
     */
    public function test_guest_cannot_access_my_orders_page(): void
    {
        $response = $this->get(route('orders.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * 2. Authenticated user can access My Orders page.
     */
    public function test_authenticated_user_can_access_my_orders_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertViewIs('orders.index');
        $response->assertSee('My Orders');
    }

    /**
     * 3. Empty state is displayed when the customer has no orders.
     */
    public function test_empty_state_displayed_when_user_has_no_orders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertSee('No Orders Placed Yet');
        $response->assertSee(route('products.index'));
    }

    /**
     * 4. My Orders list only displays orders belonging to the authenticated customer.
     */
    public function test_my_orders_only_displays_own_orders_and_never_other_users(): void
    {
        $userA = User::factory()->create(['name' => 'Alice Customer', 'email' => 'alice@test.com']);
        $userB = User::factory()->create(['name' => 'Bob Stranger', 'email' => 'bob@test.com']);

        $orderA1 = $this->createOrderForUser($userA, ['order_number' => 'ALX-ALICE-001', 'total' => 899000]);
        $orderA2 = $this->createOrderForUser($userA, ['order_number' => 'ALX-ALICE-002', 'total' => 1099000]);
        $orderB = $this->createOrderForUser($userB, ['order_number' => 'ALX-BOB-999', 'total' => 1299000]);

        $response = $this->actingAs($userA)->get(route('orders.index'));

        $response->assertStatus(200);
        // Alice should see her own orders
        $response->assertSee('ALX-ALICE-001');
        $response->assertSee('ALX-ALICE-002');
        $response->assertSee('Rp 899.000');
        $response->assertSee('Rp 1.099.000');

        // Alice must NEVER see Bob's order
        $response->assertDontSee('ALX-BOB-999');
        $response->assertDontSee('Rp 1.299.000');
    }

    /**
     * 5. Orders are sorted in descending order (latest first).
     */
    public function test_orders_are_sorted_latest_first(): void
    {
        $user = User::factory()->create();

        $olderOrder = $this->createOrderForUser($user, ['order_number' => 'ALX-OLDER-001', 'created_at' => now()->subDays(5)]);
        $newerOrder = $this->createOrderForUser($user, ['order_number' => 'ALX-NEWER-002', 'created_at' => now()]);

        $response = $this->actingAs($user)->get(route('orders.index'));

        $response->assertStatus(200);
        $orders = $response->viewData('orders');
        $this->assertEquals('ALX-NEWER-002', $orders->first()->order_number);
    }

    /**
     * 6. Order card links to correct orders.show detail route.
     */
    public function test_order_card_links_to_order_detail(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderForUser($user, ['order_number' => 'ALX-CLICK-123']);

        $response = $this->actingAs($user)->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertSee(route('orders.show', 'ALX-CLICK-123'));
    }

    /**
     * 7. Pagination works correctly when user has more than 10 orders.
     */
    public function test_pagination_works_when_many_orders_exist(): void
    {
        $user = User::factory()->create();

        // Create 12 orders for this user
        for ($i = 1; $i <= 12; $i++) {
            $this->createOrderForUser($user, [
                'order_number' => sprintf('ALX-PAG-%03d', $i),
                'created_at' => now()->addMinutes($i),
            ]);
        }

        // Page 1 should contain 10 orders
        $page1Response = $this->actingAs($user)->get(route('orders.index'));
        $page1Response->assertStatus(200);
        $this->assertCount(10, $page1Response->viewData('orders'));

        // Page 2 should contain 2 orders
        $page2Response = $this->actingAs($user)->get(route('orders.index', ['page' => 2]));
        $page2Response->assertStatus(200);
        $this->assertCount(2, $page2Response->viewData('orders'));
    }

    /**
     * 8. Navigation includes My Orders link in desktop dropdown and mobile drawer for authenticated users.
     */
    public function test_navigation_displays_my_orders_link_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee(route('orders.index'));
        $response->assertSee('My Orders');
    }

    /**
     * 9. Customer cannot view another customer's order detail directly via orders.show (403 forbidden).
     */
    public function test_customer_cannot_view_another_customer_order_detail(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $orderB = $this->createOrderForUser($userB, ['order_number' => 'ALX-PRIVATE-888']);

        $response = $this->actingAs($userA)->get(route('orders.show', $orderB->order_number));

        $response->assertStatus(403);
    }
}
