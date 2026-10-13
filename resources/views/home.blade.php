@extends('layouts.app')

@section('title', 'Alexa — Refined Hydration')
@section('meta_description', 'Alexa creates premium, minimal insulated drinkware designed for considered daily routines.')

@section('content')
<!-- Hero Section -->
<section style="min-height: calc(85vh - 5rem); display: flex; align-items: center; border-bottom: 1px solid var(--color-border); background-color: var(--color-bg); padding: 4rem 0;">
    <div class="container-custom" style="width: 100%;">
        <div style="display: grid; grid-template-columns: 1fr; gap: 3.5rem; align-items: center;">
            <div style="max-width: 680px;">
                <span class="eyebrow">Alexa Hydration Collection</span>
                <h1 class="serif" style="font-size: clamp(2.5rem, 5vw, 4.25rem); line-height: 1.05; font-weight: 400; color: var(--color-text-main); letter-spacing: -0.02em; margin-bottom: 1.75rem;">
                    Refined hydration for considered daily routines.
                </h1>
                <p style="font-size: 1.125rem; color: var(--color-text-muted); line-height: 1.7; margin-bottom: 2.5rem; max-width: 540px;">
                    Simplicity, refined proportions, and reliable temperature retention. Designed to accompany you effortlessly from morning routines to evening journeys.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                    <a href="{{ route('products.index') }}" class="btn-editorial btn-editorial-primary" id="hero-cta-explore">
                        Explore Collection
                    </a>
                    <a href="#story" class="btn-editorial btn-editorial-outline" id="hero-cta-story">
                        Our Philosophy
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Collection Highlights Section -->
<section style="padding: 6rem 0; border-bottom: 1px solid var(--color-border); background-color: var(--color-surface);">
    <div class="container-custom">
        <div style="display: flex; flex-direction: column; md:flex-row; justify-content: space-between; align-items: flex-start; margin-bottom: 3.5rem;">
            <span class="eyebrow">The Series</span>
            <h2 class="serif" style="font-size: 2.25rem; font-weight: 400; letter-spacing: -0.01em;">
                Considered Proportions for Every Need
            </h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
            <!-- Alexa Core Highlight -->
            <a href="{{ route('products.index', ['collection' => 'alexa-core']) }}" style="background: #FFFFFF; border: 1px solid var(--color-border); padding: 2.5rem 2rem; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease;" class="collection-highlight-card">
                <div>
                    <span class="eyebrow" style="color: var(--color-text-subtle);">Everyday Companion</span>
                    <h3 class="serif" style="font-size: 1.5rem; font-weight: 400; margin-bottom: 0.75rem; color: var(--color-text-main);">Alexa Core</h3>
                    <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.6; margin-bottom: 1.5rem;">
                        Designed for the everyday. Clean silhouette and 500 ml capacity tailored for desk work and morning commutes.
                    </p>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1.5rem; border-top: 1px solid var(--color-surface-subtle);">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-main); font-weight: 500;">500 ml &bull; Screw Cap</span>
                    <span style="font-size: 0.875rem; font-weight: 500;">Rp 899.000</span>
                </div>
            </a>

            <!-- Alexa Active Highlight -->
            <a href="{{ route('products.index', ['collection' => 'alexa-active']) }}" style="background: #FFFFFF; border: 1px solid var(--color-border); padding: 2.5rem 2rem; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease;" class="collection-highlight-card">
                <div>
                    <span class="eyebrow" style="color: var(--color-text-subtle);">Movement & Fitness</span>
                    <h3 class="serif" style="font-size: 1.5rem; font-weight: 400; margin-bottom: 0.75rem; color: var(--color-text-main);">Alexa Active</h3>
                    <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.6; margin-bottom: 1.5rem;">
                        Made to move. Functional sport spout and integrated carry handle in a versatile 600 ml form factor.
                    </p>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1.5rem; border-top: 1px solid var(--color-surface-subtle);">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-main); font-weight: 500;">600 ml &bull; Sport Spout</span>
                    <span style="font-size: 0.875rem; font-weight: 500;">Rp 1.099.000</span>
                </div>
            </a>

            <!-- Alexa Trek Highlight -->
            <a href="{{ route('products.index', ['collection' => 'alexa-trek']) }}" style="background: #FFFFFF; border: 1px solid var(--color-border); padding: 2.5rem 2rem; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease;" class="collection-highlight-card">
                <div>
                    <span class="eyebrow" style="color: var(--color-text-subtle);">Travel & Outdoor</span>
                    <h3 class="serif" style="font-size: 1.5rem; font-weight: 400; margin-bottom: 0.75rem; color: var(--color-text-main);">Alexa Trek</h3>
                    <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.6; margin-bottom: 1.5rem;">
                        Built for the journey. Generous 750 ml capacity and wide-mouth build designed for longer days away.
                    </p>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1.5rem; border-top: 1px solid var(--color-surface-subtle);">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-main); font-weight: 500;">750 ml &bull; Wide-Mouth</span>
                    <span style="font-size: 0.875rem; font-weight: 500;">Rp 1.299.000</span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section style="padding: 6rem 0; background-color: var(--color-bg); border-bottom: 1px solid var(--color-border);">
    <div class="container-custom">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--color-border);">
            <div>
                <span class="eyebrow">Catalog Showcase</span>
                <h2 class="serif" style="font-size: 2.25rem; font-weight: 400; letter-spacing: -0.01em;">
                    The Complete Collection
                </h2>
            </div>
            <a href="{{ route('products.index') }}" class="nav-link" style="font-weight: 500; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 0.5rem;" id="home-view-all-link">
                View All Products &rarr;
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2.5rem;">
            @foreach($products as $product)
                @php
                    $defaultVar = $product->defaultVariant();
                    $isInStock = $product->variants->where('status', 'active')->some(fn($v) => $v->isInStock());
                    $minPrice = $product->variants->where('status', 'active')->min('price') ?? 0;
                @endphp
                <article class="product-card" id="featured-product-{{ $product->slug }}">
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
                            <h3 class="serif" style="font-size: 1.25rem; font-weight: 400;">
                                <a href="{{ route('products.show', $product) }}" style="color: var(--color-text-main);">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            <span style="font-size: 0.9375rem; font-weight: 500; color: var(--color-text-main);">
                                Rp {{ number_format($minPrice, 0, ',', '.') }}
                            </span>
                        </div>

                        <!-- Color Swatches -->
                        <div class="color-swatches">
                            @foreach($product->variants->where('status', 'active') as $variant)
                                <span class="color-swatch-dot" style="background-color: {{ $variant->color_hex ?? '#CCCCCC' }};" title="{{ $variant->color_name }}"></span>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<!-- Brand Features & Specifications Section -->
<section id="specifications" style="padding: 5rem 0; background-color: var(--color-surface); border-bottom: 1px solid var(--color-border);">
    <div class="container-custom">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 3rem; text-align: left;">
            <div>
                <span class="eyebrow" style="color: var(--color-text-main);">Thermal Insulation</span>
                <h3 class="serif" style="font-size: 1.375rem; font-weight: 400; margin-bottom: 0.75rem;">Insulated Performance</h3>
                <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.6;">
                    Double-wall vacuum construction engineered to preserve optimal beverage temperature throughout your daily routine.
                </p>
            </div>

            <div>
                <span class="eyebrow" style="color: var(--color-text-main);">Pure Material</span>
                <h3 class="serif" style="font-size: 1.375rem; font-weight: 400; margin-bottom: 0.75rem;">18/8 Stainless Steel</h3>
                <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.6;">
                    Food-grade stainless steel interior and exterior ensuring pure taste with zero flavor retention or unwanted condensation.
                </p>
            </div>

            <div>
                <span class="eyebrow" style="color: var(--color-text-main);">Purposeful Form</span>
                <h3 class="serif" style="font-size: 1.375rem; font-weight: 400; margin-bottom: 0.75rem;">Refined Design</h3>
                <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.6;">
                    Understated proportions, comfortable tactile finishes, and leak-proof lids curated for effortless carry wherever you go.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Brand Editorial Statement Section -->
<section id="story" style="padding: 7rem 0; background-color: #141414; color: #FFFFFF;">
    <div class="container-custom">
        <div style="max-width: 780px; margin: 0 auto; text-align: center;">
            <span class="eyebrow" style="color: #8E8D88;">Design Philosophy</span>
            <h2 class="serif" style="font-size: clamp(2rem, 3.5vw, 3rem); font-weight: 400; line-height: 1.25; margin-bottom: 2rem; color: #FFFFFF;">
                Objects of daily utility, refined for effortless living.
            </h2>
            <p style="font-size: 1rem; color: #A0A09C; line-height: 1.8; margin-bottom: 2.5rem;">
                At Alexa, we believe good design is quiet. It does not demand attention; instead, it integrates naturally into your day. Each bottle is shaped with precise proportions, resilient materials, and thoughtful details so you can focus on what matters.
            </p>
            <a href="{{ route('products.index') }}" class="btn-editorial" style="background: transparent; color: #FFFFFF; border: 1px solid #FFFFFF;" id="story-cta-explore">
                Explore All Products
            </a>
        </div>
    </div>
</section>
@endsection
