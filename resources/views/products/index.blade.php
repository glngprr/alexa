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
        }
    }

    @media (min-width: 1024px) {
        .products-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 3rem;
        }
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
                    <a href="{{ route('products.show', $product) }}" class="product-image-container">
                        @if($defaultVar && $defaultVar->image)
                            <img src="{{ asset($defaultVar->image) }}" alt="{{ $product->name }} in {{ $defaultVar->color_name }}">
                        @endif

                        @if($isInStock)
                            <span class="stock-badge">In Stock</span>
                        @else
                            <span class="stock-badge out-of-stock">Out of Stock</span>
                        @endif
                    </a>

                    <div style="padding-top: 1.25rem;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); display: block; margin-bottom: 0.25rem;">
                            {{ $product->collection->name ?? 'Alexa' }} &bull; {{ $product->capacity }}
                        </span>
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem;">
                            <h2 class="serif" style="font-size: 1.25rem; font-weight: 400;">
                                <a href="{{ route('products.show', $product) }}" style="color: var(--color-text-main);">
                                    {{ $product->name }}
                                </a>
                            </h2>
                            <span style="font-size: 0.9375rem; font-weight: 500; color: var(--color-text-main);">
                                Rp {{ number_format($minPrice, 0, ',', '.') }}
                            </span>
                        </div>

                        <!-- Color Swatches -->
                        <div class="color-swatches">
                            @foreach($activeVars as $variant)
                                <span class="color-swatch-dot" style="background-color: {{ $variant->color_hex ?? '#CCCCCC' }};" title="{{ $variant->color_name }}"></span>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
