@extends('layouts.app')

@section('title', 'My Orders — Alexa')
@section('meta_description', 'View your order history, payment settlement states, and shipment tracking for Alexa drinkware.')

@push('styles')
<style>
    .order-history-card {
        background: #FFFFFF;
        border: 1px solid var(--color-border);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .order-history-card:hover {
        border-color: var(--color-border-dark);
    }

    .order-card-header {
        padding: 1.5rem 1.75rem;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        background: var(--color-surface);
    }

    .order-card-body {
        padding: 1.75rem;
    }

    .order-card-footer {
        padding: 1.25rem 1.75rem;
        border-top: 1px solid var(--color-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        background: #FFFFFF;
    }

    .order-item-row {
        display: grid;
        grid-template-columns: 4rem 1fr auto;
        gap: 1.25rem;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid var(--color-surface-subtle);
    }

    .order-item-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .order-item-row:first-child {
        padding-top: 0;
    }

    .order-item-thumb {
        width: 4rem;
        height: 4rem;
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.375rem;
        overflow: hidden;
    }

    .order-item-thumb img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.3125rem 0.75rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 500;
        border-radius: 2px;
        line-height: 1;
    }

    .status-badge-pending {
        background-color: #FFF8E6;
        color: #8F6B00;
        border: 1px solid #FFE6A3;
    }

    .status-badge-paid, .status-badge-processing, .status-badge-delivered {
        background-color: #F0FDF4;
        color: #15803D;
        border: 1px solid #BBF7D0;
    }

    .status-badge-shipped, .status-badge-in_transit {
        background-color: #EFF6FF;
        color: #1E40AF;
        border: 1px solid #BFDBFE;
    }

    .status-badge-failed, .status-badge-cancelled {
        background-color: #FEF2F2;
        color: #991B1B;
        border: 1px solid #FECACA;
    }

    .status-badge-expired {
        background-color: #F3F4F6;
        color: #4B5563;
        border: 1px solid #E5E7EB;
    }

    .pagination-wrap {
        margin-top: 3rem;
        display: flex;
        justify-content: center;
    }

    .pagination-wrap nav {
        display: flex;
        gap: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-custom" style="padding-top: 2.5rem; padding-bottom: 7rem;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" aria-label="Breadcrumb" style="display: flex; gap: 0.5rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); margin-bottom: 2.5rem;">
        <a href="{{ route('home') }}" style="color: var(--color-text-muted);">Home</a>
        <span>/</span>
        <span style="color: var(--color-text-main);">My Orders</span>
    </nav>

    <!-- Header -->
    <div style="margin-bottom: 3rem;">
        <span class="eyebrow">Customer Account</span>
        <h1 class="serif" style="font-size: clamp(2.25rem, 4vw, 3rem); font-weight: 400; letter-spacing: -0.01em; margin-bottom: 0.5rem;">
            My Orders
        </h1>
        <p style="font-size: 0.9375rem; color: var(--color-text-muted); line-height: 1.6; max-width: 580px;">
            Review your purchase history, check settlement confirmations, and track parcel dispatches.
        </p>
    </div>

    @if($orders->isEmpty())
        <!-- Empty State -->
        <div style="padding: 6rem 2rem; text-align: center; background: var(--color-surface); border: 1px solid var(--color-border);" id="empty-orders-state">
            <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 1.5rem; color: var(--color-text-subtle);">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <h2 class="serif" style="font-size: 1.75rem; font-weight: 400; margin-bottom: 0.75rem;">No Orders Placed Yet</h2>
            <p style="font-size: 0.9375rem; color: var(--color-text-muted); margin-bottom: 2rem; max-width: 440px; margin-left: auto; margin-right: auto; line-height: 1.6;">
                You haven't placed any orders yet. Discover our collection of considered insulated stainless steel drinkware.
            </p>
            <a href="{{ route('products.index') }}" class="btn-editorial btn-editorial-primary" id="btn-empty-explore">
                Explore Collection
            </a>
        </div>
    @else
        <!-- Orders List -->
        <div class="orders-list" style="display: flex; flex-direction: column; gap: 2rem;" id="orders-list">
            @foreach($orders as $order)
                @php
                    $oStatus = strtolower($order->order_status);
                    $oBadgeClass = match($oStatus) {
                        'processing' => 'status-badge-processing',
                        'shipped' => 'status-badge-shipped',
                        'delivered' => 'status-badge-delivered',
                        'cancelled' => 'status-badge-cancelled',
                        default => 'status-badge-pending',
                    };

                    $pStatus = strtolower($order->paymentStatus());
                    $pBadgeClass = match($pStatus) {
                        'paid' => 'status-badge-paid',
                        'failed' => 'status-badge-failed',
                        'expired' => 'status-badge-expired',
                        'cancelled' => 'status-badge-cancelled',
                        default => 'status-badge-pending',
                    };
                @endphp
                <article class="order-history-card" id="order-card-{{ $order->order_number }}">
                    <!-- Card Header -->
                    <div class="order-card-header">
                        <div>
                            <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); display: block; margin-bottom: 0.25rem;">
                                Order Reference
                            </span>
                            <span class="serif" style="font-size: 1.125rem; font-weight: 500; color: var(--color-text-main);">
                                {{ $order->order_number }}
                            </span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                            <div>
                                <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); display: block; margin-bottom: 0.25rem;">
                                    Date Placed
                                </span>
                                <span style="font-size: 0.8125rem; color: var(--color-text-main); font-weight: 500;">
                                    {{ $order->created_at->format('M d, Y') }}
                                </span>
                            </div>

                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <span class="status-badge {{ $pBadgeClass }}" title="Payment Status">
                                    {{ ucfirst($pStatus) }}
                                </span>
                                <span class="status-badge {{ $oBadgeClass }}" title="Order Status">
                                    {{ ucfirst($order->order_status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body: Order Items Preview -->
                    <div class="order-card-body">
                        @foreach($order->orderItems as $item)
                            @php
                                $variant = $item->productVariant;
                            @endphp
                            <div class="order-item-row">
                                <div class="order-item-thumb">
                                    @if($variant && $variant->image)
                                        <img src="{{ asset($variant->image) }}" alt="{{ $item->product_name }}">
                                    @else
                                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.2" color="var(--color-text-subtle)">
                                            <rect x="6" y="2" width="12" height="20" rx="2"></rect>
                                        </svg>
                                    @endif
                                </div>

                                <div>
                                    <h4 class="serif" style="font-size: 1rem; font-weight: 400; margin-bottom: 0.25rem;">
                                        {{ $item->product_name }}
                                    </h4>
                                    <span style="font-size: 0.75rem; color: var(--color-text-muted); display: block;">
                                        Color: <strong style="color: var(--color-text-main); font-weight: 500;">{{ $item->variant_name }}</strong> &bull; Qty: {{ $item->quantity }} &bull; SKU: {{ $item->sku }}
                                    </span>
                                </div>

                                <div style="text-align: right;">
                                    <span style="font-size: 0.875rem; font-weight: 500; color: var(--color-text-main);">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Card Footer: Summary & Actions -->
                    <div class="order-card-footer">
                        <div>
                            <span style="font-size: 0.75rem; color: var(--color-text-muted);">
                                Total Amount:
                            </span>
                            <span class="serif" style="font-size: 1.125rem; font-weight: 600; color: var(--color-text-main); margin-left: 0.35rem;">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </span>
                        </div>

                        <div style="display: flex; gap: 0.75rem; align-items: center;">
                            <a href="{{ route('orders.show', $order->order_number) }}" class="btn-editorial btn-editorial-outline" style="padding: 0.5rem 1.25rem; font-size: 0.75rem;" id="link-order-details-{{ $order->order_number }}">
                                View Order Details &rarr;
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if($orders->hasPages())
            <div class="pagination-wrap">
                {{ $orders->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
