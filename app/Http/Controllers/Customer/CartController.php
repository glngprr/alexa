<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the authenticated user's shopping cart.
     */
    public function index(Request $request): View
    {
        $cart = $request->user()->cart()->firstOrCreate([]);

        $cart->load([
            'cartItems.productVariant.product.collection',
        ]);

        return view('cart.index', compact('cart'));
    }

    /**
     * Add a product variant to the shopping cart.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $variant = ProductVariant::with('product')->findOrFail($validated['variant_id']);

        // Check if variant or parent product is active
        if ($variant->status !== 'active' || !$variant->product || $variant->product->status !== 'active') {
            return back()->with('error', 'The selected product variant is currently unavailable.');
        }

        $cart = $request->user()->cart()->firstOrCreate([]);
        $cartItem = $cart->cartItems()->where('product_variant_id', $variant->id)->first();

        $requestedQty = max(1, (int) ($validated['quantity'] ?? 1));
        $newQty = $cartItem ? $cartItem->quantity + $requestedQty : $requestedQty;

        // Stock validation
        if ($variant->stock <= 0) {
            return back()->with('error', "{$variant->name} is currently out of stock.");
        }

        if ($newQty > $variant->stock) {
            return back()->with('error', "Cannot add requested quantity. Only {$variant->stock} items available in stock.");
        }

        if ($cartItem) {
            $cartItem->update(['quantity' => $newQty]);
        } else {
            $cart->cartItems()->create([
                'product_variant_id' => $variant->id,
                'quantity' => $newQty,
            ]);
        }

        return redirect()->route('cart.index')->with('success', "Added {$variant->product->name} ({$variant->color_name}) to your cart.");
    }

    /**
     * Update the quantity of a cart item.
     */
    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        // Authorization check
        if ($cartItem->cart->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to this cart item.');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $variant = $cartItem->productVariant;

        // Check if variant or product is inactive
        if (!$variant || $variant->status !== 'active' || !$variant->product || $variant->product->status !== 'active') {
            return back()->with('error', 'This product variant is currently unavailable.');
        }

        $targetQty = (int) $validated['quantity'];

        // Stock validation
        if ($targetQty > $variant->stock) {
            return back()->with('error', "Cannot update quantity. Only {$variant->stock} items available in stock.");
        }

        $cartItem->update(['quantity' => $targetQty]);

        return redirect()->route('cart.index')->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        // Authorization check
        if ($cartItem->cart->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to this cart item.');
        }

        $cartItem->delete();

        return redirect()->route('cart.index')->with('success', 'Item removed from your cart.');
    }
}
