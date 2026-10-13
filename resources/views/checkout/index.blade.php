@extends('layouts.app')

@section('title', 'Checkout — Alexa')
@section('meta_description', 'Complete your order and enter shipping details for your Alexa drinkware.')

@push('styles')
<style>
    .checkout-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 3.5rem;
        align-items: flex-start;
    }

    @media (min-width: 1024px) {
        .checkout-layout {
            grid-template-columns: 1.5fr 1fr;
            gap: 4.5rem;
        }
    }

    .form-section {
        background: #FFFFFF;
        border: 1px solid var(--color-border);
        padding: 2.5rem 2rem;
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }

    @media (min-width: 640px) {
        .form-grid-2 {
            grid-template-columns: 1fr 1fr;
        }
    }

    .form-label {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-weight: 500;
        color: var(--color-text-muted);
        margin-bottom: 0.5rem;
    }

    .form-input, .form-textarea {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 1px solid var(--color-border);
        font-size: 0.875rem;
        font-family: var(--font-sans);
        color: var(--color-text-main);
        background: #FFFFFF;
        outline: none;
        transition: border-color 0.2s ease;
    }

    .form-input:focus, .form-textarea:focus {
        border-color: var(--color-text-main);
    }

    .summary-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        padding: 2.25rem 2rem;
    }

    .summary-item-row {
        display: grid;
        grid-template-columns: 3.5rem 1fr auto;
        gap: 1rem;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid var(--color-border);
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
</style>
@endpush

@section('content')
<div class="container-custom" style="padding-top: 2.5rem; padding-bottom: 7rem;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" aria-label="Breadcrumb" style="display: flex; gap: 0.5rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); margin-bottom: 2.5rem;">
        <a href="{{ route('home') }}" style="color: var(--color-text-muted);">Home</a>
        <span>/</span>
        <a href="{{ route('cart.index') }}" style="color: var(--color-text-muted);">Shopping Cart</a>
        <span>/</span>
        <span style="color: var(--color-text-main);">Checkout</span>
    </nav>

    <div style="margin-bottom: 3rem;">
        <span class="eyebrow">Order Placement</span>
        <h1 class="serif" style="font-size: clamp(2.25rem, 4vw, 3rem); font-weight: 400; letter-spacing: -0.01em;">
            Checkout
        </h1>
    </div>

    <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
        @csrf
        <div class="checkout-layout">
            <!-- Left Column: Shipping Address -->
            <div>
                <div class="form-section">
                    <h2 class="serif" style="font-size: 1.375rem; font-weight: 400; margin-bottom: 1.75rem; letter-spacing: -0.01em;">
                        Shipping Information
                    </h2>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="recipient_name" class="form-label">Recipient Name *</label>
                            <input type="text" name="recipient_name" id="recipient_name" 
                                   value="{{ old('recipient_name', $latestAddress?->recipient_name ?? auth()->user()->name) }}" 
                                   class="form-input" required autofocus>
                            @error('recipient_name')
                                <span style="font-size: 0.75rem; color: #842029; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" name="phone" id="phone" 
                                   value="{{ old('phone', $latestAddress?->phone) }}" 
                                   placeholder="08123456789"
                                   class="form-input" required>
                            @error('phone')
                                <span style="font-size: 0.75rem; color: #842029; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address" class="form-label">Full Street Address *</label>
                        <textarea name="address" id="address" rows="3" class="form-textarea" required 
                                  placeholder="Street name, building number, apartment/suite">{{ old('address', $latestAddress?->address) }}</textarea>
                        @error('address')
                            <span style="font-size: 0.75rem; color: #842029; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="city" class="form-label">City *</label>
                            <input type="text" name="city" id="city" 
                                   value="{{ old('city', $latestAddress?->city) }}" 
                                   class="form-input" required>
                            @error('city')
                                <span style="font-size: 0.75rem; color: #842029; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="province" class="form-label">Province / State *</label>
                            <input type="text" name="province" id="province" 
                                   value="{{ old('province', $latestAddress?->province) }}" 
                                   class="form-input" required>
                            @error('province')
                                <span style="font-size: 0.75rem; color: #842029; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="postal_code" class="form-label">Postal Code *</label>
                        <input type="text" name="postal_code" id="postal_code" 
                               value="{{ old('postal_code', $latestAddress?->postal_code) }}" 
                               class="form-input" style="max-width: 240px;" required>
                        @error('postal_code')
                            <span style="font-size: 0.75rem; color: #842029; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <a href="{{ route('cart.index') }}" style="font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-muted); display: inline-flex; align-items: center; gap: 0.5rem;">
                        &larr; Back to Shopping Cart
                    </a>
                </div>
            </div>

            <!-- Right Column: Order Review & Confirmation -->
            <div>
                <div class="summary-card">
                    <h2 class="serif" style="font-size: 1.375rem; font-weight: 400; margin-bottom: 1.5rem; letter-spacing: -0.01em;">
                        Order Summary
                    </h2>

                    <!-- Items Preview -->
                    <div style="margin-bottom: 1.5rem;">
                        @foreach($cart->cartItems as $item)
                            @php
                                $variant = $item->productVariant;
                                $product = $variant?->product;
                            @endphp
                            <div class="summary-item-row">
                                <div style="background: #FFFFFF; aspect-ratio: 1/1; padding: 0.25rem; border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: center;">
                                    @if($variant && $variant->image)
                                        <img src="{{ asset($variant->image) }}" alt="{{ $variant->name }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                    @endif
                                </div>
                                <div>
                                    <h4 class="serif" style="font-size: 0.9375rem; font-weight: 400; margin-bottom: 0.125rem;">
                                        {{ $product ? $product->name : 'Product' }}
                                    </h4>
                                    <span style="font-size: 0.75rem; color: var(--color-text-muted); display: block;">
                                        {{ $variant->color_name ?? '-' }} &bull; Qty: {{ $item->quantity }}
                                    </span>
                                </div>
                                <div style="font-size: 0.875rem; font-weight: 500; color: var(--color-text-main);">
                                    Rp {{ number_format($item->subtotal(), 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Financial Summary -->
                    <div class="summary-row">
                        <span>Items Subtotal</span>
                        <span style="font-weight: 500; color: var(--color-text-main);">
                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="summary-row">
                        <span>Shipping Delivery</span>
                        <span style="color: #2D5A27; font-weight: 500;">Complimentary (Rp 0)</span>
                    </div>

                    <div class="summary-row total-row">
                        <span class="serif">Total Due</span>
                        <span class="serif" style="font-size: 1.375rem; font-weight: 600;">
                            Rp {{ number_format($total, 0, ',', '.') }}
                        </span>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn-editorial btn-editorial-primary" style="width: 100%; height: 3.25rem;" id="btn-place-order">
                            Confirm &amp; Place Order
                        </button>
                        <p style="font-size: 0.6875rem; text-align: center; color: var(--color-text-subtle); margin-top: 0.875rem; text-transform: uppercase; letter-spacing: 0.08em; line-height: 1.5;">
                            Secure checkout &bull; 1-year limited manufacturer warranty
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
