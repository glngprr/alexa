@extends('layouts.app')

@section('title', ($selectedCollection ? $selectedCollection->name . ' — ' : '') . 'Products — Alexa')
@section('meta_description', $selectedCollection ? $selectedCollection->description : 'Discover the Alexa hydration collection. Premium insulated bottles crafted with refined proportions for daily life.')

@push('styles')
<style>
    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        margin-bottom: 3.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--color-border);
    }

    .filter-tab {
        display: inline-flex;
        align-items: center;
        padding: 0.625rem 1.25rem;
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-weight: 500;
        color: var(--color-text-muted);
        border: 1px solid var(--color-border);
        background: #FFFFFF;
        transition: all 0.2s ease;
    }

    .filter-tab:hover {
        color: var(--color-text-main);
        border-color: var(--color-text-main);
    }

    .filter-tab.active {
        background: var(--color-text-main);
        color: #FFFFFF;
        border-color: var(--color-text-main);
    }

    .products-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2.5rem;
    }

    @media (min-width: 640px) {
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }
    }

    @media (min-width: 1024px) {
        .products-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 2.5rem;
        }
    }

    .product-card {
        display: flex;
        flex-direction: column;
        position: relative;
    }

    /* Primary link wrapping image and title */
    .product-card-link {
        display: block;
        text-decoration: none;
        color: inherit;
    }

    /* Clean editorial image container: 4:5 portrait ratio */
    .product-image-container {
        position: relative;
        aspect-ratio: 4 / 5;
        background-color: transparent;
        overflow: hidden;
        margin-bottom: 1rem;
        display: block;
    }

    .product-card-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center center;
        display: block;
        /* Calibrated 1.18x scale and 3.5% vertical offset to trim studio whitespace
           while preserving complete bottle caps/handles, bases, and cast shadows */
        transform: scale(1.18) translateY(3.5%);
        transform-origin: center center;
        transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .product-card:hover .product-card-img {
        transform: scale(1.22) translateY(3.5%);
    }

    /* Inline stock status badge matching editorial design */
    .stock-badge {
        display: inline-block;
        font-size: 0.6875rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-weight: 500;
        color: #2D5A27;
    }

    .stock-badge.out-of-stock {
        color: #842029;
    }

    .product-card-meta {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--color-text-subtle);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
        margin-bottom: 0.3rem;
        line-height: 1.4;
    }

    .product-card-meta .meta-sep {
        color: var(--color-border);
    }

    .product-card-title-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 0.5rem;
        margin-bottom: 0.625rem;
    }

    .product-card-name {
        font-size: 1.35rem;
        font-weight: 400;
        color: var(--color-text-main);
        letter-spacing: -0.01em;
        transition: color 0.2s ease;
    }

    .product-card:hover .product-card-name {
        color: #000000;
    }

    .product-card-price {
        font-size: 0.9375rem;
        font-weight: 500;
        color: var(--color-text-main);
        white-space: nowrap;
    }

    /* Color Swatches */
    .color-swatches {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-top: 0.25rem;
    }

    .color-swatch-dot {
        width: 15px;
        height: 15px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.18);
        display: inline-block;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .color-swatch-dot:hover {
        transform: scale(1.2);
        box-shadow: 0 0 0 1px var(--color-text-main);
    }
</style>
@endpush

@section('content')
<div class="container-custom" style="padding-top: 3.5rem; padding-bottom: 6rem;">
    <!-- Page Header -->
    <div style="margin-bottom: 3rem; max-width: 720px;">
        <span class="eyebrow">The Catalog</span>
        <h1 class="serif" style="font-size: clamp(2.25rem, 4vw, 3.25rem); font-weight: 400; line-height: 1.1; margin-bottom: 1rem; letter-spacing: -0.01em;">
            {{ $selectedCollection ? $selectedCollection->name : 'All Products' }}
        </h1>
        <p style="font-size: 1rem; color: var(--color-text-muted); line-height: 1.7;">
            {{ $selectedCollection && $selectedCollection->description ? $selectedCollection->description : 'Explore insulated stainless steel bottles designed with purposeful simplicity, durable craft, and timeless character.' }}
        </p>
    </div>

    <!-- Collection Filter Tabs -->
    <div class="filter-bar" id="collection-filter-bar">
        <a href="{{ route('products.index') }}" class="filter-tab {{ !$selectedCollection ? 'active' : '' }}" id="filter-all">
            All Products
        </a>
        @foreach($collections as $collection)
            <a href="{{ route('products.index', ['collection' => $collection->slug]) }}" 
               class="filter-tab {{ $selectedCollection && $selectedCollection->slug === $collection->slug ? 'active' : '' }}"
               id="filter-{{ $collection->slug }}">
                {{ $collection->name }}
            </a>
        @endforeach
    </div>

    <!-- Products Grid -->
    @if($products->isEmpty())
        <div style="padding: 4rem 0; text-align: center;">
            <p class="serif" style="font-size: 1.5rem; color: var(--color-text-muted); margin-bottom: 1rem;">No products found.</p>
            <a href="{{ route('products.index') }}" class="btn-editorial btn-editorial-outline">View All Products</a>
        </div>
    @else
        <div class="products-grid">
            @foreach($products as $product)
                @php
                    $defaultVar = $product->defaultVariant();
                    $activeVars = $product->variants->where('status', 'active');
                    $isInStock = $activeVars->some(fn($v) => $v->isInStock());
                    $minPrice = $activeVars->min('price') ?? 0;
                @endphp
                <article class="product-card" id="product-card-{{ $product->slug }}">
                    <a href="{{ route('products.show', $product) }}" class="product-card-link" aria-label="View {{ $product->name }}">
                        <!-- Clean editorial image container: 4:5 ratio, scaled bottle, no badge overlay -->
                        <div class="product-image-container">
                            @if($defaultVar && $defaultVar->image)
                                <img src="{{ asset($defaultVar->image) }}" 
                                     alt="{{ $product->name }} in {{ $defaultVar->color_name }}"
                                     class="product-card-img">
                            @endif
                        </div>

                        <div>
                            <!-- Stock status inline in metadata, not overlaid on image -->
                            <span class="product-card-meta">
                                {{ $product->collection->name ?? 'Alexa' }} &bull; {{ $product->capacity }}
                                <span class="meta-sep">&bull;</span>
                                @if($isInStock)
                                    <span class="stock-badge">In Stock</span>
                                @else
                                    <span class="stock-badge out-of-stock">Out of Stock</span>
                                @endif
                            </span>
                            <div class="product-card-title-row">
                                <h2 class="serif product-card-name">
                                    {{ $product->name }}
                                </h2>
                                <span class="product-card-price">
                                    Rp {{ number_format($minPrice, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </a>

                    <!-- Color Swatches -->
                    <div class="color-swatches" aria-label="Available color variants for {{ $product->name }}">
                        @foreach($activeVars as $variant)
                            <a href="{{ route('products.show', $product) }}" 
                               class="color-swatch-dot" 
                               style="background-color: {{ $variant->color_hex ?? '#CCCCCC' }};" 
                               title="{{ $variant->color_name }}"
                               aria-label="{{ $variant->color_name }}"></a>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
