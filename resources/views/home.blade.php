@extends('layouts.app')

@section('title', 'Alexa — Refined Hydration')
@section('meta_description', 'Alexa creates premium, minimal insulated drinkware designed for considered daily routines.')

@push('styles')
<style>
    /* ==========================================================================
       HERO SECTION STYLES
       ========================================================================== */
    .hero-editorial {
        position: relative;
        min-height: clamp(560px, calc(90vh - 5rem), 900px);
        display: flex;
        align-items: center;
        border-bottom: 1px solid var(--color-border);
        background-color: #EFEAE3;
        overflow: hidden;
    }

    /* Background media container */
    .hero-media-wrapper {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        pointer-events: none;
        overflow: hidden;
    }

    .hero-bg-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        /* Use contain so the entire bottle is visible from handle to base */
        object-fit: contain;
        /* Anchor to the right so the bottle shows on right, background fills left */
        object-position: right center;
        display: block;
        transform: translateZ(0);
    }

    /* Editorial soft gradient overlay to guarantee text legibility without dimming the bottle */
    .hero-gradient-overlay {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            rgba(242, 238, 232, 0.96) 0%,
            rgba(242, 238, 232, 0.90) 34%,
            rgba(242, 238, 232, 0.55) 54%,
            rgba(242, 238, 232, 0.0) 72%
        );
    }

    .hero-content-container {
        position: relative;
        z-index: 2;
        width: 100%;
        padding-top: 4.5rem;
        padding-bottom: 4.5rem;
    }

    .hero-copy-block {
        max-width: 600px;
    }

    .hero-eyebrow {
        letter-spacing: 0.22em;
        font-weight: 500;
        color: #5A5A56;
        margin-bottom: 1.25rem;
    }

    .hero-headline {
        font-size: clamp(2.35rem, 4.5vw, 4rem);
        line-height: 1.08;
        font-weight: 400;
        color: var(--color-text-main);
        letter-spacing: -0.025em;
        margin-bottom: 1.5rem;
    }

    .hero-description {
        font-size: 1.0625rem;
        color: #4A4A46;
        line-height: 1.7;
        margin-bottom: 2.5rem;
        max-width: 520px;
    }

    .hero-cta-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem;
    }

    /* ==========================================================================
       SERIES HIGHLIGHTS SECTION
       ========================================================================== */
    .series-section {
        padding: 6rem 0;
        border-bottom: 1px solid var(--color-border);
        background-color: var(--color-surface);
    }

    .section-header-row {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 3.5rem;
    }

    @media (min-width: 768px) {
        .section-header-row {
            flex-direction: row;
            align-items: flex-end;
        }
    }

    .series-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.75rem;
    }

    @media (min-width: 768px) {
        .series-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
    }

    @media (min-width: 1024px) {
        .series-grid {
            gap: 2rem;
        }
    }

    .series-card {
        background: #FFFFFF;
        border: 1px solid var(--color-border);
        padding: 2.5rem 2rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 320px;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), 
                    border-color 0.3s ease, 
                    box-shadow 0.3s ease;
        text-decoration: none;
    }

    .series-card:hover {
        transform: translateY(-4px);
        border-color: #D2D2CC;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.04);
    }

    .series-card-eyebrow {
        color: var(--color-text-subtle);
        margin-bottom: 0.5rem;
    }

    .series-card-title {
        font-size: 1.5rem;
        font-weight: 400;
        margin-bottom: 0.875rem;
        color: var(--color-text-main);
        letter-spacing: -0.01em;
    }

    .series-card-desc {
        font-size: 0.875rem;
        color: var(--color-text-muted);
        line-height: 1.65;
        margin-bottom: 2rem;
    }

    .series-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--color-surface-subtle);
    }

    .series-spec-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--color-text-main);
        font-weight: 500;
        flex: 1;
        min-width: 0;
    }

    .series-price-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--color-text-main);
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* ==========================================================================
       FEATURED PRODUCTS SHOWCASE SECTION
       Clean, frame-free editorial styling
       ========================================================================== */
    .showcase-section {
        padding: 6.5rem 0;
        background-color: var(--color-bg);
        border-bottom: 1px solid var(--color-border);
    }

    .showcase-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 3.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--color-border);
    }

    .showcase-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 3rem;
    }

    @media (min-width: 640px) {
        .showcase-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
    }

    @media (min-width: 1024px) {
        .showcase-grid {
            gap: 2.5rem;
        }
    }

    .product-editorial-card {
        display: flex;
        flex-direction: column;
        position: relative;
    }

    /* Clickable primary block wrapping image and text */
    .product-editorial-link {
        display: block;
        text-decoration: none;
        color: inherit;
    }

    /* Clean editorial image container: portrait ratio matches product photo proportions */
    .product-editorial-image-wrap {
        position: relative;
        /* 4:5 portrait — more compact than 2:3, proportional to the product photos
           while keeping the showcase from feeling too tall */
        aspect-ratio: 4 / 5;
        background-color: transparent;
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .product-editorial-img {
        width: 100%;
        height: 100%;
        /* contain as baseline, combined with calibrated scale to trim studio whitespace */
        object-fit: contain;
        object-position: center center;
        display: block;
        /* Scale 1.18x with 3.5% vertical shift gives comfortable top margin
           for tall handles (Trek/Active) while eliminating excess studio margins */
        transform: scale(1.18) translateY(3.5%);
        transform-origin: center center;
        transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .product-editorial-card:hover .product-editorial-img {
        transform: scale(1.22) translateY(3.5%);
    }

    /* Stock status label — inline metadata, NOT overlaid on image */
    .stock-editorial-badge {
        display: inline-block;
        font-size: 0.6875rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-weight: 500;
        color: #2D5A27;
    }

    .stock-editorial-badge.out-of-stock {
        color: #842029;
    }

    .product-editorial-meta {
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

    /* Separator between collection/capacity and stock status */
    .product-editorial-meta .meta-sep {
        color: var(--color-border);
    }

    .product-editorial-title-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 0.5rem;
        margin-bottom: 0.625rem;
    }

    .product-editorial-name {
        font-size: 1.35rem;
        font-weight: 400;
        color: var(--color-text-main);
        letter-spacing: -0.01em;
        transition: color 0.2s ease;
    }

    .product-editorial-card:hover .product-editorial-name {
        color: #000000;
    }

    .product-editorial-price {
        font-size: 0.9375rem;
        font-weight: 500;
        color: var(--color-text-main);
        white-space: nowrap;
    }

    /* Color Swatches */
    .swatches-editorial-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-top: 0.25rem;
    }

    .swatch-editorial-dot {
        width: 15px;
        height: 15px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.18);
        display: inline-block;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .swatch-editorial-dot:hover {
        transform: scale(1.2);
        box-shadow: 0 0 0 1px var(--color-text-main);
    }

    /* ==========================================================================
       BENEFITS & SPECIFICATIONS SECTION
       ========================================================================== */
    .specifications-section {
        padding: 5.5rem 0;
        background-color: var(--color-surface);
        border-bottom: 1px solid var(--color-border);
        scroll-margin-top: 5.5rem;
    }

    .specifications-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 3.5rem;
        text-align: left;
    }

    .spec-item-title {
        font-size: 1.375rem;
        font-weight: 400;
        margin-bottom: 0.75rem;
        color: var(--color-text-main);
    }

    .spec-item-desc {
        font-size: 0.875rem;
        color: var(--color-text-muted);
        line-height: 1.65;
    }

    /* ==========================================================================
       DESIGN PHILOSOPHY SECTION (DARK EDITORIAL)
       ========================================================================== */
    .philosophy-section {
        padding: 7.5rem 0;
        background-color: #141414;
        color: #FFFFFF;
        scroll-margin-top: 5.5rem;
    }

    .philosophy-inner {
        max-width: 780px;
        margin: 0 auto;
        text-align: center;
    }

    .philosophy-eyebrow {
        color: #8E8D88;
        margin-bottom: 1.25rem;
    }

    .philosophy-title {
        font-size: clamp(2.15rem, 3.8vw, 3.25rem);
        font-weight: 400;
        line-height: 1.2;
        margin-bottom: 1.75rem;
        color: #FFFFFF;
        letter-spacing: -0.015em;
    }

    .philosophy-desc {
        font-size: 1.0625rem;
        color: #A0A09C;
        line-height: 1.85;
        margin-bottom: 2.75rem;
        max-width: 660px;
        margin-left: auto;
        margin-right: auto;
    }

    .btn-philosophy-cta {
        background: transparent;
        color: #FFFFFF;
        border: 1px solid #FFFFFF;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-philosophy-cta:hover {
        background: #FFFFFF;
        color: #141414;
    }

    /* ==========================================================================
       RESPONSIVE BREAKPOINTS
       ========================================================================== */
    /* Tablet & Medium Screens (768px - 1024px) */
    @media (max-width: 1024px) {
        .hero-editorial {
            min-height: 520px;
        }

        .hero-bg-image {
            object-position: right center;
        }

        .hero-gradient-overlay {
            background: linear-gradient(
                90deg,
                rgba(242, 238, 232, 0.97) 0%,
                rgba(242, 238, 232, 0.92) 42%,
                rgba(242, 238, 232, 0.55) 65%,
                rgba(242, 238, 232, 0.0) 84%
            );
        }

        .hero-copy-block {
            max-width: 480px;
        }
    }

    /* ──────────────────────────────────────────────────────────────────────────
       MOBILE HERO — stacked layout (max-width: 767px)
       On mobile the background-image approach (side-by-side) breaks down:
       bottle overlaps text and CTA wraps. We switch to a portrait-stack:
       image area on top (via a tall pseudo-element + object-fit contain),
       copy block below — both clearly separated, no overlap.
    ────────────────────────────────────────────────────────────────────────── */
    @media (max-width: 767px) {
        /* Make the hero taller to accommodate stacked layout */
        .hero-editorial {
            min-height: auto;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            overflow: hidden;
        }

        /* Background image: pulled up and centered so bottle dominates the
           upper portion. Opacity stays full — gradient handles fade. */
        .hero-bg-image {
            object-fit: contain;
            object-position: center top;
            opacity: 1;
        }

        /* Image wrapper becomes a fixed-height top band (~52 vw tall) */
        .hero-media-wrapper {
            position: relative;      /* take up flow space */
            height: 52vw;
            min-height: 200px;
            max-height: 260px;
            flex-shrink: 0;
            background-color: #EFEAE3;
        }

        /* Soft bottom-fade so the image blends into the copy section */
        .hero-gradient-overlay {
            background: linear-gradient(
                to bottom,
                transparent 30%,
                rgba(239, 234, 227, 0.6) 70%,
                #EFEAE3 100%
            );
        }

        /* Content container becomes a normal block below the image band */
        .hero-content-container {
            position: relative;
            z-index: 2;
            padding-top: 1.75rem;
            padding-bottom: 3rem;
            background-color: #EFEAE3;
        }

        /* Copy block takes full width — no artificial width cap */
        .hero-copy-block {
            max-width: 100%;
        }

        .hero-eyebrow {
            font-size: 0.6875rem;
            letter-spacing: 0.18em;
            margin-bottom: 0.875rem;
        }

        .hero-headline {
            font-size: clamp(1.75rem, 7vw, 2.25rem);
            line-height: 1.12;
            margin-bottom: 1rem;
        }

        .hero-description {
            font-size: 0.9375rem;
            line-height: 1.65;
            margin-bottom: 1.75rem;
            max-width: 100%;
        }

        /* CTA row: two buttons side-by-side, each 50% minus gap. 
           At very narrow widths they wrap gracefully. */
        .hero-cta-group {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 0.625rem;
        }

        .hero-cta-group .btn-editorial {
            flex: 1 1 auto;
            min-width: 0;
            padding: 0.875rem 1rem;
            font-size: 0.75rem;
            letter-spacing: 0.11em;
            text-align: center;
            white-space: nowrap;
        }
    }

    /* Small Tablet & intermediate (481px - 767px) — already covered above */
    /* Desktop-only adjustments that were previously in the 768px block: */
    @media (max-width: 1024px) and (min-width: 768px) {
        .hero-copy-block {
            max-width: 480px;
        }

        .hero-headline {
            font-size: 2.35rem;
            line-height: 1.12;
        }

        .hero-description {
            font-size: 0.95rem;
        }

        .hero-cta-group {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
    }

    /* Sections padding on mobile */
    @media (max-width: 767px) {
        .series-section,
        .showcase-section,
        .specifications-section,
        .philosophy-section {
            padding: 4rem 0;
        }

        .philosophy-desc {
            font-size: 0.9375rem;
        }

        .series-card {
            padding: 2.25rem 1.75rem;
            min-height: auto;
        }

        .specifications-grid {
            gap: 2.5rem;
        }

        .btn-philosophy-cta {
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-size: 0.75rem;
        }
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero-editorial" id="hero">
    <!-- Hero Media Backdrop -->
    <div class="hero-media-wrapper" aria-hidden="true">
        <img src="{{ asset('images/homepage/hero.png') }}" 
             alt="" 
             class="hero-bg-image" 
             fetchpriority="high">
        <div class="hero-gradient-overlay"></div>
    </div>

    <!-- Hero Content Container -->
    <div class="container-custom hero-content-container">
        <div class="hero-copy-block">
            <span class="eyebrow hero-eyebrow">ALEXA HYDRATION COLLECTION</span>
            <h1 class="serif hero-headline">
                Designed to go wherever the day takes you.
            </h1>
            <p class="hero-description">
                Reliable temperature retention, practical details, and timeless design for your daily commute, workouts, and outdoor journeys.
            </p>
            <div class="hero-cta-group">
                <a href="{{ route('products.index') }}" class="btn-editorial btn-editorial-primary" id="hero-cta-explore">
                    DISCOVER THE COLLECTION
                </a>
                <a href="#story" class="btn-editorial btn-editorial-outline" id="hero-cta-story">
                    OUR PHILOSOPHY
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Collection Highlights / Shop by Series Section -->
<section class="series-section" id="series">
    <div class="container-custom">
        <div class="section-header-row">
            <div>
                <span class="eyebrow">The Series</span>
                <h2 class="serif" style="font-size: 2.25rem; font-weight: 400; letter-spacing: -0.01em;">
                    Considered Proportions for Every Need
                </h2>
            </div>
        </div>

        @php
            $coreProduct = $products->firstWhere('slug', 'alexa-core');
            $activeProduct = $products->firstWhere('slug', 'alexa-active');
            $trekProduct = $products->firstWhere('slug', 'alexa-trek');

            $corePrice = $coreProduct?->variants->where('status', 'active')->min('price') ?? 899000;
            $activePrice = $activeProduct?->variants->where('status', 'active')->min('price') ?? 1099000;
            $trekPrice = $trekProduct?->variants->where('status', 'active')->min('price') ?? 1299000;
        @endphp

        <div class="series-grid">
            <!-- Alexa Core Highlight (Penggunaan sehari-hari) -->
            <a href="{{ route('products.index', ['collection' => 'alexa-core']) }}" class="series-card" id="series-card-core">
                <div>
                    <span class="eyebrow series-card-eyebrow">Everyday Companion</span>
                    <h3 class="serif series-card-title">Alexa Core</h3>
                    <p class="series-card-desc">
                        Designed for the everyday. Clean silhouette and 500 ml capacity tailored for desk work, morning commutes, and considered daily hydration.
                    </p>
                </div>
                <div class="series-card-footer">
                    <span class="series-spec-label">{{ $coreProduct?->capacity ?? '500 ml' }} &bull; {{ $coreProduct?->lid_type ?? 'Screw cap' }}</span>
                    <span class="series-price-label">Rp {{ number_format($corePrice, 0, ',', '.') }}</span>
                </div>
            </a>

            <!-- Alexa Active Highlight (Aktivitas, olahraga, dan mobilitas) -->
            <a href="{{ route('products.index', ['collection' => 'alexa-active']) }}" class="series-card" id="series-card-active">
                <div>
                    <span class="eyebrow series-card-eyebrow">Movement & Fitness</span>
                    <h3 class="serif series-card-title">Alexa Active</h3>
                    <p class="series-card-desc">
                        Made to move. Functional sport spout and integrated carry handle built for workouts, active sports, and daily mobility.
                    </p>
                </div>
                <div class="series-card-footer">
                    <span class="series-spec-label">{{ $activeProduct?->capacity ?? '600 ml' }} &bull; {{ $activeProduct?->lid_type ?? 'Sport spout with integrated carry handle' }}</span>
                    <span class="series-price-label">Rp {{ number_format($activePrice, 0, ',', '.') }}</span>
                </div>
            </a>

            <!-- Alexa Trek Highlight (Perjalanan dan aktivitas outdoor) -->
            <a href="{{ route('products.index', ['collection' => 'alexa-trek']) }}" class="series-card" id="series-card-trek">
                <div>
                    <span class="eyebrow series-card-eyebrow">Travel & Outdoor</span>
                    <h3 class="serif series-card-title">Alexa Trek</h3>
                    <p class="series-card-desc">
                        Built for the journey. Generous 750 ml capacity and durable wide-mouth build crafted for travel, longer days away, and outdoor journeys.
                    </p>
                </div>
                <div class="series-card-footer">
                    <span class="series-spec-label">{{ $trekProduct?->capacity ?? '750 ml' }} &bull; {{ $trekProduct?->lid_type ?? 'Wide-mouth with integrated carry handle' }}</span>
                    <span class="series-price-label">Rp {{ number_format($trekPrice, 0, ',', '.') }}</span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="showcase-section" id="featured-products">
    <div class="container-custom">
        <div class="showcase-header-row">
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

        <div class="showcase-grid">
            @foreach($products as $product)
                @php
                    $defaultVar = $product->defaultVariant();
                    $activeVariants = $product->variants->where('status', 'active');
                    $isInStock = $activeVariants->some(fn($v) => $v->isInStock());
                    $minPrice = $activeVariants->min('price') ?? 0;
                @endphp
                <article class="product-editorial-card" id="featured-product-{{ $product->slug }}">
                    <!-- Entire product area clickable to product detail -->
                    <a href="{{ route('products.show', $product) }}" class="product-editorial-link" aria-label="View {{ $product->name }}">
                        <!-- Clean editorial image: no badge overlay, no border, no shadow -->
                        <div class="product-editorial-image-wrap">
                            @if($defaultVar && $defaultVar->image)
                                <img src="{{ asset($defaultVar->image) }}" 
                                     alt="{{ $product->name }} in {{ $defaultVar->color_name }}"
                                     class="product-editorial-img">
                            @endif
                        </div>

                        <div>
                            <!-- Stock status inline in metadata, not overlaid on image -->
                            <span class="product-editorial-meta">
                                {{ $product->collection->name ?? 'Alexa' }} &bull; {{ $product->capacity }}
                                <span class="meta-sep">&bull;</span>
                                @if($isInStock)
                                    <span class="stock-editorial-badge">In Stock</span>
                                @else
                                    <span class="stock-editorial-badge out-of-stock">Out of Stock</span>
                                @endif
                            </span>
                            <div class="product-editorial-title-row">
                                <h3 class="serif product-editorial-name">
                                    {{ $product->name }}
                                </h3>
                                <span class="product-editorial-price">
                                    Rp {{ number_format($minPrice, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </a>

                    <!-- Color Swatches -->
                    <div class="swatches-editorial-row" aria-label="Available color variants for {{ $product->name }}">
                        @foreach($activeVariants as $variant)
                            <a href="{{ route('products.show', $product) }}" 
                               class="swatch-editorial-dot" 
                               style="background-color: {{ $variant->color_hex ?? '#CCCCCC' }};" 
                               title="{{ $variant->color_name }}"
                               aria-label="{{ $variant->color_name }}"></a>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<!-- Brand Features & Specifications Section -->
<section id="specifications" class="specifications-section">
    <div class="container-custom">
        <div class="specifications-grid">
            <div>
                <span class="eyebrow" style="color: var(--color-text-main);">Thermal Insulation</span>
                <h3 class="serif spec-item-title">Insulated Performance</h3>
                <p class="spec-item-desc">
                    Double-wall vacuum construction engineered to preserve optimal beverage temperature throughout your daily routine.
                </p>
            </div>

            <div>
                <span class="eyebrow" style="color: var(--color-text-main);">Pure Material</span>
                <h3 class="serif spec-item-title">18/8 Stainless Steel</h3>
                <p class="spec-item-desc">
                    Food-grade stainless steel interior and exterior ensuring pure taste with zero flavor retention or unwanted condensation.
                </p>
            </div>

            <div>
                <span class="eyebrow" style="color: var(--color-text-main);">Purposeful Form</span>
                <h3 class="serif spec-item-title">Refined Design</h3>
                <p class="spec-item-desc">
                    Understated proportions, comfortable tactile finishes, and leak-proof lids curated for effortless carry wherever you go.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Brand Editorial Statement Section -->
<section id="story" class="philosophy-section">
    <div class="container-custom">
        <div class="philosophy-inner">
            <span class="eyebrow philosophy-eyebrow">Design Philosophy</span>
            <h2 class="serif philosophy-title">
                Objects of daily utility, refined for effortless living.
            </h2>
            <p class="philosophy-desc">
                At Alexa, we believe good design is quiet. It does not demand attention; instead, it integrates naturally into your day. Each bottle is shaped with precise proportions, resilient materials, and thoughtful details so you can focus on what matters.
            </p>
            <a href="{{ route('products.index') }}" class="btn-editorial btn-philosophy-cta" id="story-cta-explore">
                Explore All Products
            </a>
        </div>
    </div>
</section>
@endsection
