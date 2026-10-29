<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
    }

    /**
     * 1. Guest navbar does not display Register / Create Account links.
     */
    public function test_guest_navbar_does_not_contain_register_link(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Guest sees Sign In
        $response->assertSee(route('login'));

        // Guest does not see direct register / create account link in navbar/drawer
        $response->assertDontSee('id="nav-register-btn"', false);
        $response->assertDontSee('id="mobile-nav-register"', false);
        $response->assertDontSee('>Register<', false);
        $response->assertDontSee('>Create Account<', false);
    }

    /**
     * 2. Register page is accessible directly via GET /register.
     */
    public function test_register_page_is_accessible_to_guest(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Join Alexa');
        $response->assertSee('Email Address');
        $response->assertSee('Full Name');
    }

    /**
     * 3. Login page contains link to Create Account (Register).
     */
    public function test_login_page_contains_create_account_link(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee(route('register'));
        $response->assertSee('Create an Account');
    }

    /**
     * 4. Normal login redirects default to /products (product catalog).
     */
    public function test_standard_login_redirects_to_products_catalog(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('Password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'Password123',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 5. Intended URL is respected when logging in after hitting protected page.
     */
    public function test_login_respects_intended_url_for_protected_routes(): void
    {
        $user = User::factory()->create([
            'email' => 'shopper@example.com',
            'password' => Hash::make('Password123'),
        ]);

        // Attempting to visit /orders redirects to login and records intended URL
        $protectedResponse = $this->get('/orders');
        $protectedResponse->assertRedirect(route('login'));

        // Logging in now should take user to intended (/orders) instead of fallback (/products)
        $loginResponse = $this->post('/login', [
            'email' => 'shopper@example.com',
            'password' => 'Password123',
        ]);

        $loginResponse->assertRedirect(route('orders.index'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 6. Registration creates user, logs them in, and redirects to /products.
     */
    public function test_registration_creates_user_and_redirects_to_products(): void
    {
        $response = $this->post('/register', [
            'name' => 'Alice Doe',
            'email' => 'alice@example.com',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'Alice Doe',
            'email' => 'alice@example.com',
        ]);
    }

    /**
     * 7. Flash notification is rendered once on order detail page, not twice.
     */
    public function test_notification_is_rendered_only_once(): void
    {
        $user = User::factory()->create();

        $address = Address::create([
            'user_id' => $user->id,
            'recipient_name' => $user->name,
            'phone' => '08123456789',
            'address' => 'Jl. Jenderal Sudirman No. 10',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ALX-FLASH-TEST',
            'shipping_address_id' => $address->id,
            'subtotal' => 899000,
            'shipping_cost' => 0,
            'total' => 899000,
            'order_status' => 'pending',
        ]);

        $variant = ProductVariant::first();
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

        $uniqueMessage = 'Payment test confirmation unique string 98765';

        $response = $this->actingAs($user)
            ->withSession(['success' => $uniqueMessage])
            ->get(route('orders.show', $order));

        $response->assertStatus(200);

        // Assert the flash message appears exactly once in HTML output
        $content = $response->getContent();
        $this->assertSame(
            1,
            substr_count($content, $uniqueMessage),
            'Notification flash message should be rendered exactly once.'
        );
    }
}
