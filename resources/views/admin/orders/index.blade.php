@extends('layouts.admin')

@section('title', 'Customer Orders — Alexa Admin')

@section('content')
<div class="page-header">
    <div>
        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); display: block; margin-bottom: 0.25rem;">Operations</span>
        <h1 class="page-title">Order Management &amp; Fulfillment</h1>
        <p class="page-subtitle">Track purchases, verify payment settlement states, and manage courier shipment dispatches.</p>
    </div>
</div>

<!-- Filters Bar -->
<div class="card" style="padding: 1.25rem 1.5rem; margin-bottom: 1.5rem;">
    <form action="{{ route('admin.orders.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 200px;">
            <label class="form-label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Order number, customer name, email..." class="form-input" id="search-orders">
        </div>

        <div style="flex: 1; min-width: 150px;">
            <label class="form-label">Order Status</label>
            <select name="status" class="form-select">
                <option value="">All Order Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <div style="flex: 1; min-width: 150px;">
            <label class="form-label">Payment Status</label>
            <select name="payment_status" class="form-select">
                <option value="">All Payment Statuses</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="expired" {{ request('payment_status') === 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn-editorial btn-editorial-primary" style="padding: 0.625rem 1rem;">
                Filter
            </button>
            @if(request()->anyFilled(['q', 'status', 'payment_status']))
                <a href="{{ route('admin.orders.index') }}" class="btn-editorial btn-editorial-outline" style="padding: 0.625rem 1rem;">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    @if($orders->count() > 0)
        <div class="admin-table-wrap" style="border: none;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th>Shipment</th>
                        <th style="text-align: right; width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>
                                <strong style="font-family: monospace; font-size: 0.8125rem;">
                                    <a href="{{ route('admin.orders.show', $order->order_number) }}">{{ $order->order_number }}</a>
                                </strong>
                            </td>
                            <td>
                                <div style="font-weight: 500;">{{ $order->user->name ?? 'Guest/Anonymous' }}</div>
                                <div style="font-size: 0.75rem; color: var(--color-text-muted);">{{ $order->user->email ?? '-' }}</div>
                            </td>
                            <td>
                                {{ $order->created_at->format('M d, Y H:i') }}
                            </td>
                            <td>
                                <strong style="font-weight: 600;">Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                @php
                                    $pStatus = $order->paymentStatus();
                                    $pBadge = match($pStatus) {
                                        'paid' => 'status-badge-paid',
                                        'failed' => 'status-badge-failed',
                                        'expired' => 'status-badge-expired',
                                        'cancelled' => 'status-badge-cancelled',
                                        default => 'status-badge-pending',
                                    };
                                @endphp
                                <span class="status-badge {{ $pBadge }}" id="payment-badge-{{ $order->order_number }}">{{ ucfirst($pStatus) }}</span>
                            </td>
                            <td>
                                @php
                                    $oStatus = $order->order_status;
                                    $oBadge = match($oStatus) {
                                        'processing' => 'status-badge-processing',
                                        'shipped', 'packed' => 'status-badge-shipped',
                                        'delivered' => 'status-badge-delivered',
                                        'cancelled' => 'status-badge-cancelled',
                                        default => 'status-badge-pending',
                                    };
                                @endphp
                                <span class="status-badge {{ $oBadge }}" id="order-badge-{{ $order->order_number }}">{{ ucfirst($oStatus) }}</span>
                            </td>
                            <td>
                                @if($order->shipment)
                                    <span class="status-badge {{ $order->shipment->isDelivered() ? 'status-badge-delivered' : 'status-badge-shipped' }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->shipment->status)) }}
                                    </span>
                                @else
                                    <span style="font-size: 0.75rem; color: var(--color-text-subtle);">Unfulfilled</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.orders.show', $order->order_number) }}" class="btn-editorial btn-editorial-outline" style="padding: 0.375rem 0.75rem; font-size: 0.6875rem;" id="btn-view-order-{{ $order->order_number }}">
                                    Manage &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--color-border);">
                {{ $orders->links() }}
            </div>
        @endif
    @else
        <div style="padding: 3.5rem; text-align: center; color: var(--color-text-muted);">
            <p style="font-size: 1rem;">No orders match the current filter or search criteria.</p>
        </div>
    @endif
</div>
@endsection
