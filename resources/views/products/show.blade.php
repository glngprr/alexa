@extends('layouts.app')

@section('title', $product->name . ' — Alexa')
@section('meta_description', Str::limit($product->description, 160))

@push('styles')
<style>
    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--color-text-subtle);
        margin-bottom: 2.5rem;
    }

    .breadcrumb-link {
        color: var(--color-text-muted);
        transition: color 0.2s ease;
    }

    .breadcrumb-link:hover {
        color: var(--color-text-main);
    }

    .product-detail-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 3.5rem;
        align-items: flex-start;
    }

    @media (min-width: 900px) {
        .product-detail-layout {
            grid-template-columns: 1.1fr 1fr;
            gap: 5rem;
        }
    }

    .gallery-container {
        position: relative;
        background-color: transparent;
        aspect-ratio: 4 / 5;
        overflow: hidden;
        display: block;
        max-width: 540px;
        width: 100%;
        margin-left: auto;
        margin-right: auto;
    }

    @media (min-width: 900px) {
        .gallery-container {
            max-width: none;
            margin: 0;
        }
    }

    .gallery-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center center;
        display: block;
        /* Calibrated 1.18x scale and 3.5% vertical offset to trim studio whitespace
           while preserving complete bottle caps/handles, bases, and cast shadows */
        transform: scale(1.18) translateY(3.5%);
        transform-origin: center center;
        transition: opacity 0.3s ease, transform 0.4s ease;
    }

    .variant-swatch-button {
        position: relative;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        padding: 2px;
    }

    .variant-swatch-button:hover {
        border-color: var(--color-text-muted);
    }

    .variant-swatch-button.selected {
        border-color: var(--color-text-main);
    }

    .variant-swatch-circle {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.15);
    }

    .spec-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 2rem;
        font-size: 0.875rem;
    }

    .spec-table tr {
        border-bottom: 1px solid var(--color-border);
    }

    .spec-table th {
        text-align: left;
        padding: 0.875rem 0;
        font-weight: 400;
        color: var(--color-text-muted);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.1em;
        width: 38%;
    }

    .spec-table td {
        padding: 0.875rem 0;
        color: var(--color-text-main);
        font-weight: 500;
    }

    .badge-availability {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 500;
        padding: 0.35rem 0.75rem;
        border-radius: 2px;
    }

    .badge-in-stock {
        background-color: #F0F5F0;
        color: #2D5A27;
        border: 1px solid #D5E5D3;
    }

    .badge-out-of-stock {
        background-color: #F8F4F4;
        color: #8C3030;
        border: 1px solid #EAD4D4;
    }

    /* Quantity & Action Controls */
    .quantity-picker {
        display: inline-flex;
        align-items: center;
        border: 1px solid var(--color-border);
        background: #FFFFFF;
        height: 3.25rem;
    }

    .quantity-btn {
        padding: 0 1.125rem;
        font-size: 1.125rem;
        line-height: 1;
        color: var(--color-text-main);
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s ease;
    }

    .quantity-btn:hover {
        background-color: var(--color-surface);
    }

    .quantity-input {
        width: 3.25rem;
        text-align: center;
        border: none;
        font-size: 0.9375rem;
        font-family: var(--font-sans);
        font-weight: 500;
        outline: none;
        background: transparent;
        -moz-appearance: textfield;
    }

    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .btn-add-cart:disabled {
        background-color: #A0A09C !important;
        border-color: #A0A09C !important;
        cursor: not-allowed;
        opacity: 0.7;
    }
</style>
@endpush

@section('content')
<div class="container-custom" style="padding-top: 2.5rem; padding-bottom: 6rem;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="breadcrumb-link">Home</a>
        <span>/</span>
        <a href="{{ route('products.index') }}" class="breadcrumb-link">Products</a>
        @if($product->collection)
            <span>/</span>
            <a href="{{ route('products.index', ['collection' => $product->collection->slug]) }}" class="breadcrumb-link">
                {{ $product->collection->name }}
            </a>
        @endif
        <span>/</span>
        <span style="color: var(--color-text-main);" aria-current="page">{{ $product->name }}</span>
    </nav>

    @php
        $defaultVariant = $product->defaultVariant();
    @endphp

    <div class="product-detail-layout">
        <!-- Product Gallery Column -->
        <div>
            <div class="gallery-container">
                <img id="main-product-image" 
                     class="gallery-image" 
                     src="{{ asset($defaultVariant ? $defaultVariant->image : '') }}" 
                     alt="{{ $product->name }}">
            </div>
        </div>

        <!-- Product Information & Variant Selector Column -->
        <div>
            <span class="eyebrow" style="color: var(--color-text-subtle);">
                {{ $product->collection->name ?? 'Alexa' }} Series
            </span>

            <h1 class="serif" style="font-size: clamp(2rem, 3.5vw, 2.75rem); font-weight: 400; line-height: 1.15; margin-bottom: 0.75rem; letter-spacing: -0.01em;">
                {{ $product->name }}
            </h1>

            <!-- Price & Availability Row -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--color-border);">
                <div id="product-price" class="serif" style="font-size: 1.5rem; font-weight: 500; color: var(--color-text-main);">
                    Rp {{ number_format($defaultVariant ? $defaultVariant->price : 0, 0, ',', '.') }}
                </div>
                <div id="product-availability">
                    @if($defaultVariant && $defaultVariant->isInStock())
                        <span class="badge-availability badge-in-stock">In Stock</span>
                    @else
                        <span class="badge-availability badge-out-of-stock">Out of Stock</span>
                    @endif
                </div>
            </div>

            <!-- Description -->
            <p style="font-size: 0.9375rem; color: var(--color-text-muted); line-height: 1.8; margin-bottom: 2rem;">
                {{ $product->description }}
            </p>

            <!-- Variant Selector -->
            <div style="margin-bottom: 2rem; padding: 1.5rem; background: var(--color-surface); border: 1px solid var(--color-border);">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-main); font-weight: 600;">
                        Color: <span id="selected-color-name" style="font-weight: 400; color: var(--color-text-muted);">{{ $defaultVariant ? $defaultVariant->color_name : '' }}</span>
                    </span>
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle);">
                        SKU: <span id="selected-variant-sku" style="color: var(--color-text-muted);">{{ $defaultVariant ? $defaultVariant->sku : '' }}</span>
                    </span>
                </div>

                <!-- Color Swatches Grid -->
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;" id="variant-swatches-container">
                    @foreach($product->variants->where('status', 'active') as $variant)
                        <button type="button"
                                class="variant-swatch-button {{ $defaultVariant && $defaultVariant->id === $variant->id ? 'selected' : '' }}"
                                data-variant-id="{{ $variant->id }}"
                                data-variant-name="{{ $variant->name }}"
                                data-variant-color="{{ $variant->color_name }}"
                                data-variant-sku="{{ $variant->sku }}"
                                data-variant-price="Rp {{ number_format($variant->price, 0, ',', '.') }}"
                                data-variant-image="{{ asset($variant->image) }}"
                                data-in-stock="{{ $variant->isInStock() ? '1' : '0' }}"
                                aria-label="Select {{ $variant->color_name }} color"
                                id="variant-swatch-{{ $variant->sku }}">
                            <span class="variant-swatch-circle" style="background-color: {{ $variant->color_hex ?? '#CCCCCC' }};"></span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Add to Cart Form -->
            <form action="{{ route('cart.items.store') }}" method="POST" id="add-to-cart-form" style="margin-bottom: 2.5rem;">
                @csrf
                <input type="hidden" name="variant_id" id="selected-variant-id" value="{{ $defaultVariant ? $defaultVariant->id : '' }}">

                <div style="display: flex; gap: 1rem; align-items: center;">
                    <!-- Quantity Picker -->
                    <div class="quantity-picker">
                        <button type="button" class="quantity-btn" id="qty-decrease" aria-label="Decrease quantity">-</button>
                        <input type="number" name="quantity" id="quantity-input" class="quantity-input" value="1" min="1" max="99" required>
                        <button type="button" class="quantity-btn" id="qty-increase" aria-label="Increase quantity">+</button>
                    </div>

                    <!-- Add to Cart Button -->
                    <button type="submit" 
                            id="add-to-cart-btn" 
                            class="btn-editorial btn-editorial-primary btn-add-cart" 
                            style="flex: 1; height: 3.25rem;"
                            {{ (!$defaultVariant || !$defaultVariant->isInStock()) ? 'disabled' : '' }}>
                        {{ ($defaultVariant && $defaultVariant->isInStock()) ? 'Add to Cart' : 'Out of Stock' }}
                    </button>
                </div>
            </form>

            <!-- Product Specifications -->
            <div style="margin-top: 2.5rem;">
                <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 0.5rem;">Specifications</h3>
                <table class="spec-table">
                    <tbody>
                        <tr>
                            <th>Capacity</th>
                            <td>{{ $product->capacity }}</td>
                        </tr>
                        <tr>
                            <th>Material</th>
                            <td>{{ $product->material }}</td>
                        </tr>
                        <tr>
                            <th>Lid Type</th>
                            <td>{{ $product->lid_type }}</td>
                        </tr>
                        <tr>
                            <th>Weight</th>
                            <td>{{ $product->weight }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const swatchButtons = document.querySelectorAll('.variant-swatch-button');
        const mainImage = document.getElementById('main-product-image');
        const selectedColorName = document.getElementById('selected-color-name');
        const selectedVariantSku = document.getElementById('selected-variant-sku');
        const productPrice = document.getElementById('product-price');
        const productAvailability = document.getElementById('product-availability');
        const variantIdInput = document.getElementById('selected-variant-id');
        const addToCartBtn = document.getElementById('add-to-cart-btn');

        const qtyInput = document.getElementById('quantity-input');
        const qtyDecrease = document.getElementById('qty-decrease');
        const qtyIncrease = document.getElementById('qty-increase');

        if (qtyDecrease && qtyInput) {
            qtyDecrease.addEventListener('click', function () {
                let currentVal = parseInt(qtyInput.value) || 1;
                if (currentVal > 1) {
                    qtyInput.value = currentVal - 1;
                }
            });
        }

        if (qtyIncrease && qtyInput) {
            qtyIncrease.addEventListener('click', function () {
                let currentVal = parseInt(qtyInput.value) || 1;
                if (currentVal < 99) {
                    qtyInput.value = currentVal + 1;
                }
            });
        }

        swatchButtons.forEach(button => {
            button.addEventListener('click', function () {
                // Update selected button styling
                swatchButtons.forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');

                // Read dataset values
                const color = this.dataset.variantColor;
                const sku = this.dataset.variantSku;
                const price = this.dataset.variantPrice;
                const imageSrc = this.dataset.variantImage;
                const inStock = this.dataset.inStock === '1';
                const variantId = this.dataset.variantId;

                // Update text elements
                if (selectedColorName) selectedColorName.textContent = color;
                if (selectedVariantSku) selectedVariantSku.textContent = sku;
                if (productPrice) productPrice.textContent = price;
                if (variantIdInput) variantIdInput.value = variantId;

                // Update availability badge & button state
                if (productAvailability) {
                    if (inStock) {
                        productAvailability.innerHTML = '<span class="badge-availability badge-in-stock">In Stock</span>';
                    } else {
                        productAvailability.innerHTML = '<span class="badge-availability badge-out-of-stock">Out of Stock</span>';
                    }
                }

                if (addToCartBtn) {
                    if (inStock) {
                        addToCartBtn.disabled = false;
                        addToCartBtn.textContent = 'Add to Cart';
                    } else {
                        addToCartBtn.disabled = true;
                        addToCartBtn.textContent = 'Out of Stock';
                    }
                }

                // Update image with smooth transition
                if (mainImage && imageSrc) {
                    mainImage.style.opacity = '0.3';
                    setTimeout(() => {
                        mainImage.src = imageSrc;
                        mainImage.style.opacity = '1';
                    }, 120);
                }
            });
        });
    });
</script>
@endpush
