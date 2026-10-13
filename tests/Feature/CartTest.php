<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogSeeder::class);
    }

    /**
     * 1. Guest cannot access cart.
     */
    public function test_guest_cannot_access_cart(): void
    {
        $response = $this->get(route('cart.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * 2. Authenticated user can access cart.
     */
    public function test_authenticated_user_can_access_cart(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('cart.index'));
        $response->assertStatus(200);
        $response->assertSee('Shopping Cart');
        $response->assertSee('Your Cart is Empty');
    }

    /**
     * 3. Authenticated user can add a variant to cart.
     */
    public function test_authenticated_user_can_add_variant_to_cart(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::where('sku', 'ALX-CORE-MID')->first();

        $response = $this->actingAs($user)->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $cartResponse = $this->actingAs($user)->get(route('cart.index'));
        $cartResponse->assertStatus(200);
        $cartResponse->assertSee('Alexa Core');
        $cartResponse->assertSee('Midnight');
        $cartResponse->assertSee('Rp 899.000');
    }

    /**
     * 4. Adding the same variant increases quantity instead of creating duplicate item.
     */
    public function test_adding_same_variant_increases_quantity(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::where('sku', 'ALX-CORE-MID')->first();

        // Add once
        $this->actingAs($user)->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        // Add again
        $this->actingAs($user)->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        $cart = $user->cart;
        $this->assertCount(1, $cart->cartItems);
        $this->assertEquals(5, $cart->cartItems->first()->quantity);
    }

    /**
     * 5. Cannot add inactive variant.
     */
    public function test_cannot_add_inactive_variant(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::where('sku', 'ALX-CORE-MID')->first();
        $variant->update(['status' => 'inactive']);

        $response = $this->actingAs($user)->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('cart_items', [
            'product_variant_id' => $variant->id,
        ]);
    }

    /**
     * 6. Cannot add variant when parent product is inactive.
     */
    public function test_cannot_add_variant_when_parent_product_is_inactive(): void
    {
        $user = User::factory()->create();
        $product = Product::where('slug', 'alexa-core')->first();
        $product->update(['status' => 'inactive']);

        $variant = $product->variants->first();

        $response = $this->actingAs($user)->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('cart_items', [
            'product_variant_id' => $variant->id,
        ]);
    }

    /**
     * 7. Cannot exceed available stock when adding to cart.
     */
    public function test_cannot_exceed_available_stock_when_adding(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::where('sku', 'ALX-CORE-MID')->first();
        $variant->update(['stock' => 5]);

        // Attempting to add 6 units when stock is 5
        $response = $this->actingAs($user)->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 6,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('cart_items', [
            'product_variant_id' => $variant->id,
        ]);

        // Add 4 units (succeeds)
        $this->actingAs($user)->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 4,
        ]);

        // Attempting to add 2 more (total 6 > stock 5)
        $response2 = $this->actingAs($user)->post(route('cart.items.store'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response2->assertSessionHas('error');
        $this->assertEquals(4, $user->cart->cartItems->first()->quantity);
    }

    /**
     * 8. Can update cart quantity.
     */
    public function test_can_update_cart_quantity(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::where('sku', 'ALX-CORE-MID')->first();

        $cart = Cart::create(['user_id' => $user->id]);
        $item = $cart->cartItems()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->patch(route('cart.items.update', $item), [
            'quantity' => 4,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');
        $this->assertEquals(4, $item->fresh()->quantity);
    }

    /**
     * 9. Cannot update quantity beyond stock.
     */
    public function test_cannot_update_quantity_beyond_stock(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::where('sku', 'ALX-CORE-MID')->first();
        $variant->update(['stock' => 5]);

        $cart = Cart::create(['user_id' => $user->id]);
        $item = $cart->cartItems()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->patch(route('cart.items.update', $item), [
            'quantity' => 10,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(2, $item->fresh()->quantity);
    }

    /**
     * 10. Can remove cart item.
     */
    public function test_can_remove_cart_item(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::where('sku', 'ALX-CORE-MID')->first();

        $cart = Cart::create(['user_id' => $user->id]);
        $item = $cart->cartItems()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->delete(route('cart.items.destroy', $item));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    /**
     * 11. User cannot modify another user's cart item.
     */
    public function test_user_cannot_modify_another_users_cart_item(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $variant = ProductVariant::where('sku', 'ALX-CORE-MID')->first();

        $cart1 = Cart::create(['user_id' => $user1->id]);
        $item1 = $cart1->cartItems()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        // User2 attempts to update User1's item
        $response = $this->actingAs($user2)->patch(route('cart.items.update', $item1), [
            'quantity' => 5,
        ]);
        $response->assertStatus(403);
        $this->assertEquals(2, $item1->fresh()->quantity);

        // User2 attempts to delete User1's item
        $deleteResponse = $this->actingAs($user2)->delete(route('cart.items.destroy', $item1));
        $deleteResponse->assertStatus(403);
        $this->assertDatabaseHas('cart_items', ['id' => $item1->id]);
    }

    /**
     * 12. Cart total is calculated correctly from database prices.
     */
    public function test_cart_total_calculated_from_database_prices(): void
    {
        $user = User::factory()->create();
        $coreVariant = ProductVariant::where('sku', 'ALX-CORE-MID')->first(); // 899000
        $activeVariant = ProductVariant::where('sku', 'ALX-ACT-SNW')->first(); // 1099000

        $cart = Cart::create(['user_id' => $user->id]);
        $cart->cartItems()->create([
            'product_variant_id' => $coreVariant->id,
            'quantity' => 2, // 2 * 899000 = 1798000
        ]);
        $cart->cartItems()->create([
            'product_variant_id' => $activeVariant->id,
            'quantity' => 1, // 1 * 1099000 = 1099000
        ]);

        // Expected total: 1798000 + 1099000 = 2897000
        $this->assertEquals(2897000, $cart->total());
        $this->assertEquals(3, $cart->itemsCount());

        $response = $this->actingAs($user)->get(route('cart.index'));
        $response->assertStatus(200);
        $response->assertSee('Rp 2.897.000');
    }

    /**
     * 13. Cart belongs to the authenticated user.
     */
    public function test_cart_belongs_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $cart->user);
        $this->assertEquals($user->id, $cart->user->id);
        $this->assertInstanceOf(Cart::class, $user->cart);
        $this->assertEquals($cart->id, $user->cart->id);
    }
}
