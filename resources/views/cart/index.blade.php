@extends('layouts.app')

@section('title', 'Shopping Cart — Alexa')
@section('meta_description', 'Review items in your Alexa shopping cart.')

@push('styles')
<style>
    .cart-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 3.5rem;
        align-items: flex-start;
    }

    @media (min-width: 1024px) {
        .cart-layout {
            grid-template-columns: 1.8fr 1fr;
            gap: 4rem;
        }
    }

    .cart-item-card {
        display: grid;
        grid-template-columns: 5.5rem 1fr;
        gap: 1.5rem;
        padding: 2rem 0;
        border-bottom: 1px solid var(--color-border);
        position: relative;
    }

    @media (min-width: 640px) {
        .cart-item-card {
            grid-template-columns: 7rem 1fr auto;
            align-items: center;
        }
    }

    .cart-item-image-wrap {
        background-color: var(--color-surface);
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem;
        overflow: hidden;
    }

    .cart-item-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .cart-qty-form {
        display: inline-flex;
        align-items: center;
        border: 1px solid var(--color-border);
        background: #FFFFFF;
        height: 2.5rem;
    }

    .cart-qty-btn {
        padding: 0 0.75rem;
        font-size: 1rem;
        color: var(--color-text-main);
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .cart-qty-btn:hover {
        background-color: var(--color-surface);
    }

    .cart-qty-input {
        width: 2.75rem;
        text-align: center;
        border: none;
        font-size: 0.875rem;
        font-family: var(--font-sans);
        font-weight: 500;
        outline: none;
        background: transparent;
        -moz-appearance: textfield;
    }

    .cart-qty-input::-webkit-outer-spin-button,
    .cart-qty-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .btn-remove-item {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--color-text-subtle);
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease;
        padding: 0.25rem 0;
    }

    .btn-remove-item:hover {
        color: #842029;
    }

    .summary-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        padding: 2.25rem 2rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.875rem 0;
        font-size: 0.875rem;
        color: var(--color-text-muted);
    }

    .summary-row.total-row {
        border-top: 1px solid var(--color-border);
        margin-top: 1rem;
        padding-top: 1.25rem;
        color: var(--color-text-main);
        font-size: 1.125rem;
        font-weight: 500;
    }

    .warning-box {
        font-size: 0.75rem;
        color: #842029;
        background-color: #FDF2F2;
        border: 1px solid #F8D7DA;
        padding: 0.5rem 0.75rem;
        margin-top: 0.75rem;
        border-radius: 2px;
        display: inline-block;
    }
</style>
@endpush

@section('content')
<div class="container-custom" style="padding-top: 2.5rem; padding-bottom: 7rem;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" aria-label="Breadcrumb" style="display: flex; gap: 0.5rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); margin-bottom: 2.5rem;">
        <a href="{{ route('home') }}" style="color: var(--color-text-muted);">Home</a>
        <span>/</span>
        <a href="{{ route('products.index') }}" style="color: var(--color-text-muted);">Products</a>
        <span>/</span>
        <span style="color: var(--color-text-main);">Shopping Cart</span>
    </nav>

    <div style="margin-bottom: 3rem;">
        <span class="eyebrow">Your Selection</span>
        <h1 class="serif" style="font-size: clamp(2.25rem, 4vw, 3rem); font-weight: 400; letter-spacing: -0.01em;">
            Shopping Cart
        </h1>
    </div>

    @if(!$cart || $cart->cartItems->isEmpty())
        <!-- Empty Cart State -->
        <div style="padding: 6rem 2rem; text-align: center; background: var(--color-surface); border: 1px solid var(--color-border);">
            <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 1.5rem; color: var(--color-text-subtle);">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <h2 class="serif" style="font-size: 1.75rem; font-weight: 400; margin-bottom: 0.75rem;">Your Cart is Empty</h2>
            <p style="font-size: 0.9375rem; color: var(--color-text-muted); margin-bottom: 2rem; max-width: 420px; margin-left: auto; margin-right: auto;">
                You have no items in your shopping cart. Explore the collection to find your everyday hydration companion.
            </p>
            <a href="{{ route('products.index') }}" class="btn-editorial btn-editorial-primary" id="btn-empty-explore">
                Explore Collection
            </a>
        </div>
    @else
        <!-- Active Cart Layout -->
        <div class="cart-layout">
            <!-- Items List -->
            <div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 1rem; border-bottom: 1px solid var(--color-border); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle);">
                    <span>Item</span>
                    <span>Subtotal</span>
                </div>

                <div id="cart-items-list">
                    @foreach($cart->cartItems as $item)
                        @php
                            $variant = $item->productVariant;
                            $product = $variant?->product;
                            $isExceeded = $item->isStockExceeded();
                            $isAvailable = $item->isAvailable();
                        @endphp
                        <div class="cart-item-card" id="cart-item-{{ $item->id }}">
                            <!-- Thumbnail -->
                            <a href="{{ $product ? route('products.show', $product) : '#' }}" class="cart-item-image-wrap">
                                @if($variant && $variant->image)
                                    <img src="{{ asset($variant->image) }}" alt="{{ $variant->name }}" class="cart-item-image">
                                @endif
                            </a>

                            <!-- Details -->
                            <div>
                                <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); display: block; margin-bottom: 0.25rem;">
                                    {{ $product->collection->name ?? 'Alexa' }} Series
                                </span>
                                <h3 class="serif" style="font-size: 1.125rem; font-weight: 400; margin-bottom: 0.25rem;">
                                    @if($product)
                                        <a href="{{ route('products.show', $product) }}" style="color: var(--color-text-main);">
                                            {{ $product->name }}
                                        </a>
                                    @else
                                        {{ $variant->name ?? 'Product' }}
                                    @endif
                                </h3>
                                <p style="font-size: 0.8125rem; color: var(--color-text-muted); margin-bottom: 0.5rem;">
                                    Color: <strong style="color: var(--color-text-main); font-weight: 500;">{{ $variant->color_name ?? '-' }}</strong> &bull; SKU: {{ $variant->sku ?? '-' }}
                                </p>
                                <p style="font-size: 0.875rem; color: var(--color-text-main); font-weight: 500;">
                                    Rp {{ number_format($variant ? $variant->price : 0, 0, ',', '.') }}
                                </p>

                                @if(!$isAvailable)
                                    <div class="warning-box">
                                        This product variant is currently unavailable.
                                    </div>
                                @elseif($isExceeded)
                                    <div class="warning-box">
                                        Requested quantity exceeds available stock (Only {{ $variant->stock }} available).
                                    </div>
                                @endif

                                <!-- Quantity Controls (Mobile and Desktop) -->
                                <div style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
                                    <form action="{{ route('cart.items.update', $item) }}" method="POST" class="cart-qty-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" name="quantity" value="{{ max(1, $item->quantity - 1) }}" class="cart-qty-btn" aria-label="Decrease quantity" {{ $item->quantity <= 1 ? 'disabled' : '' }}>-</button>
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $variant ? $variant->stock : 99 }}" class="cart-qty-input" onchange="this.form.submit()">
                                        <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}" class="cart-qty-btn" aria-label="Increase quantity" {{ ($variant && $item->quantity >= $variant->stock) ? 'disabled' : '' }}>+</button>
                                    </form>

                                    <!-- Remove Form -->
                                    <form action="{{ route('cart.items.destroy', $item) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-remove-item" id="btn-remove-{{ $item->id }}">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Item Subtotal (Desktop) -->
                            <div style="text-align: right;">
                                <span class="serif" style="font-size: 1.125rem; font-weight: 500; color: var(--color-text-main); display: block;">
                                    Rp {{ number_format($item->subtotal(), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top: 2rem;">
                    <a href="{{ route('products.index') }}" style="font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-muted); display: inline-flex; align-items: center; gap: 0.5rem;" class="nav-link">
                        &larr; Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div>
                <div class="summary-card">
                    <h2 class="serif" style="font-size: 1.375rem; font-weight: 400; margin-bottom: 1.5rem; letter-spacing: -0.01em;">
                        Order Summary
                    </h2>

                    <div class="summary-row">
                        <span>Total Items</span>
                        <span style="font-weight: 500; color: var(--color-text-main);">{{ $cart->itemsCount() }} units</span>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span style="font-weight: 500; color: var(--color-text-main);">
                            Rp {{ number_format($cart->total(), 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="summary-row">
                        <span>Shipping</span>
                        <span style="color: #2D5A27; font-weight: 500;">Complimentary</span>
                    </div>

                    <div class="summary-row total-row">
                        <span class="serif">Total</span>
                        <span class="serif" style="font-size: 1.375rem; font-weight: 600;">
                            Rp {{ number_format($cart->total(), 0, ',', '.') }}
                        </span>
                    </div>

                    @if($cart->hasIssues())
                        <div style="margin-top: 1.5rem; padding: 0.875rem 1rem; background-color: #FDF2F2; border: 1px solid #F8D7DA; color: #842029; font-size: 0.8125rem; line-height: 1.5; border-radius: 2px;">
                            Please resolve item availability or stock warnings before proceeding.
                        </div>
                    @endif

                    <div style="margin-top: 2rem;">
                        @if($cart->hasIssues())
                            <button type="button" class="btn-editorial btn-editorial-primary" style="width: 100%; opacity: 0.6; cursor: not-allowed;" id="btn-proceed-checkout" disabled>
                                Proceed to Checkout
                            </button>
                        @else
                            <a href="{{ route('checkout.index') }}" class="btn-editorial btn-editorial-primary" style="width: 100%; text-align: center;" id="btn-proceed-checkout">
                                Proceed to Checkout
                            </a>
                        @endif
                        <p style="font-size: 0.6875rem; text-align: center; color: var(--color-text-subtle); margin-top: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;">
                            Complimentary shipping &amp; 1-year limited warranty
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
