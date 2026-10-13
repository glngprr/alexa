<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Display the checkout review and shipping address form.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $cart = $request->user()->cart;

        if (!$cart || $cart->cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Please add items to your cart before proceeding to checkout.');
        }

        $cart->load(['cartItems.productVariant.product.collection']);

        if ($cart->hasIssues()) {
            return redirect()->route('cart.index')->with('error', 'Please resolve item availability or stock warnings in your cart before checking out.');
        }

        $subtotal = $cart->total();
        $shippingCost = 0;
        $total = $subtotal + $shippingCost;

        $latestAddress = $request->user()->addresses()->latest('id')->first();

        return view('checkout.index', compact('cart', 'subtotal', 'shippingCost', 'total', 'latestAddress'));
    }

    /**
     * Process checkout, validate stock, and create the order transactionally.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
        ]);

        try {
            $order = DB::transaction(function () use ($request, $validated) {
                $cart = $request->user()->cart;

                if (!$cart || $cart->cartItems->isEmpty()) {
                    throw new Exception('Your cart is empty.');
                }

                $cartItems = $cart->cartItems()->get();

                // 1. Validate availability and stock with lock
                $subtotal = 0;
                $lockedVariants = [];

                foreach ($cartItems as $item) {
                    $variant = ProductVariant::lockForUpdate()->with('product')->find($item->product_variant_id);

                    if (!$variant || $variant->status !== 'active' || !$variant->product || $variant->product->status !== 'active') {
                        throw new Exception("The item '{$item->productVariant?->name}' is currently unavailable.");
                    }

                    if ($variant->stock < $item->quantity) {
                        throw new Exception("Insufficient stock for '{$variant->name}'. Only {$variant->stock} items available in stock.");
                    }

                    $itemSubtotal = (int) ($variant->price * $item->quantity);
                    $subtotal += $itemSubtotal;

                    $lockedVariants[] = [
                        'item' => $item,
                        'variant' => $variant,
                        'subtotal' => $itemSubtotal,
                    ];
                }

                // 2. Save shipping address
                $address = $request->user()->addresses()->create($validated);

                // 3. Generate unique order number
                do {
                    $orderNumber = 'ALX-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                } while (Order::where('order_number', $orderNumber)->exists());

                $shippingCost = 0;
                $total = $subtotal + $shippingCost;

                // 4. Create Order
                $order = $request->user()->orders()->create([
                    'order_number' => $orderNumber,
                    'shipping_address_id' => $address->id,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'total' => $total,
                    'order_status' => 'pending',
                ]);

                // 5. Create Order Items and safely deduct stock
                foreach ($lockedVariants as $locked) {
                    $item = $locked['item'];
                    $variant = $locked['variant'];

                    $order->orderItems()->create([
                        'product_variant_id' => $variant->id,
                        'product_name' => $variant->product->name,
                        'variant_name' => $variant->color_name,
                        'sku' => $variant->sku,
                        'unit_price' => $variant->price,
                        'quantity' => $item->quantity,
                        'subtotal' => $locked['subtotal'],
                    ]);

                    $variant->decrement('stock', $item->quantity);
                }

                // 6. Clear customer's cart
                $cart->cartItems()->delete();

                return $order;
            });

            return redirect()->route('orders.show', $order->order_number)->with('success', 'Your order has been placed successfully.');
        } catch (Exception $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Display order confirmation details for the authenticated customer.
     */
    public function show(Request $request, Order $order): View
    {
        if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load([
            'shippingAddress',
            'orderItems.productVariant',
        ]);

        return view('orders.show', compact('order'));
    }
}
