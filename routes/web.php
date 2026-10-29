<?php

use App\Http\Controllers\Admin\ShipmentController as AdminShipmentController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ProductController;
use App\Http\Controllers\Customer\TrackingController;
use App\Http\Controllers\Webhook\MidtransWebhookController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;

// Public Catalog Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Midtrans Webhook Notification (CSRF Exempt in bootstrap/app.php)
Route::post('/payments/midtrans/notification', [MidtransWebhookController::class, 'handle'])->name('payments.midtrans.notification');

// Shopping Cart & Checkout Routes (Requires Authentication)
Route::middleware('auth')->group(function () {
    // Shopping Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');

    // Customer Order History & Checkout
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders/{order}', [CheckoutController::class, 'show'])->name('orders.show');

    // Midtrans Payment Actions
    Route::post('/orders/{order}/payment', [PaymentController::class, 'pay'])->name('orders.payment');
    Route::post('/orders/{order}/payment/status', [PaymentController::class, 'checkStatus'])->name('orders.payment.status');

    // Biteship Shipping & Tracking Actions
    Route::post('/orders/{order}/shipment/track', [TrackingController::class, 'refresh'])->name('orders.shipment.track');
});

// Administrator Dashboard & Management Routes (Requires Admin Authorization)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', \App\Http\Controllers\Admin\DashboardController::class . '@index')->name('dashboard');

    // Product Management
    Route::get('/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [\App\Http\Controllers\Admin\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product:slug}/edit', [\App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product:slug}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product:slug}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');

    // Product Variant Management
    Route::post('/products/{product:slug}/variants', [\App\Http\Controllers\Admin\ProductVariantController::class, 'store'])->name('products.variants.store');
    Route::put('/variants/{variant}', [\App\Http\Controllers\Admin\ProductVariantController::class, 'update'])->name('variants.update');
    Route::delete('/variants/{variant}', [\App\Http\Controllers\Admin\ProductVariantController::class, 'destroy'])->name('variants.destroy');

    // Order Management & Fulfillment
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.status.update');
    Route::post('/orders/{order}/shipment', [AdminShipmentController::class, 'store'])->name('orders.shipment.store');
});

// Authentication endpoints for redirect handling and session login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended(route('products.index'));
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
})->name('login.post');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout');

Route::get('/register', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return view('auth.register');
})->name('register');

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
    ]);

    $user = User::create([
        'name'     => $validated['name'],
        'email'    => $validated['email'],
        'password' => Hash::make($validated['password']),
        'is_admin' => false,
    ]);

    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->route('products.index')->with('success', 'Welcome to Alexa, ' . $user->name . '!');
})->name('register.post');
