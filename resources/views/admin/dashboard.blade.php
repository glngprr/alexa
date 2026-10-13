@extends('layouts.admin')

@section('title', 'Admin Dashboard — Alexa')

@section('content')
<div class="page-header">
    <div>
        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); display: block; margin-bottom: 0.25rem;">Overview</span>
        <h1 class="page-title">Executive Dashboard</h1>
        <p class="page-subtitle">Real-time performance metrics and recent transaction summaries.</p>
    </div>
    <div>
        <a href="{{ route('admin.products.create') }}" class="btn-editorial btn-editorial-primary" id="btn-dashboard-new-product">
            + New Product
        </a>
    </div>
</div>

<!-- Metrics Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <!-- Revenue -->
    <div class="card" style="margin: 0; padding: 1.5rem;">
        <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); display: block; margin-bottom: 0.5rem; font-weight: 600;">
            Total Paid Revenue
        </span>
        <div class="serif" style="font-size: 1.75rem; font-weight: 600; color: #15803D; letter-spacing: -0.01em;" id="metric-total-revenue">
            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-text-muted); display: block; margin-top: 0.25rem;">
            Verified Midtrans settlements
        </span>
    </div>

    <!-- Total Orders -->
    <div class="card" style="margin: 0; padding: 1.5rem;">
        <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); display: block; margin-bottom: 0.5rem; font-weight: 600;">
            Total Orders
        </span>
        <div class="serif" style="font-size: 1.75rem; font-weight: 500;" id="metric-total-orders">
            {{ number_format($totalOrders) }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-text-muted); display: block; margin-top: 0.25rem;">
            Customer purchases to date
        </span>
    </div>

    <!-- Total Products -->
    <div class="card" style="margin: 0; padding: 1.5rem;">
        <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); display: block; margin-bottom: 0.5rem; font-weight: 600;">
            Catalog Products
        </span>
        <div class="serif" style="font-size: 1.75rem; font-weight: 500;" id="metric-total-products">
            {{ number_format($totalProducts) }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-text-muted); display: block; margin-top: 0.25rem;">
            Available bottle designs
        </span>
    </div>

    <!-- Total Customers -->
    <div class="card" style="margin: 0; padding: 1.5rem;">
        <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--color-text-subtle); display: block; margin-bottom: 0.5rem; font-weight: 600;">
            Active Customers
        </span>
        <div class="serif" style="font-size: 1.75rem; font-weight: 500;" id="metric-total-customers">
            {{ number_format($totalCustomers) }}
        </div>
        <span style="font-size: 0.75rem; color: var(--color-text-muted); display: block; margin-top: 0.25rem;">
            Registered customer accounts
        </span>
    </div>
</div>

<!-- Order Status Summary Row -->
<div class="card" style="margin-bottom: 2rem;">
    <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1rem;">
        Order Lifecycle Status
    </h3>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" style="flex: 1; min-width: 140px; padding: 1rem; background: var(--color-surface-subtle); border-radius: 2px; text-decoration: none;">
            <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: block;">Pending</span>
            <strong style="font-size: 1.25rem; font-weight: 600; color: #8F6B00;">{{ $statusCounts['pending'] ?? 0 }}</strong>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" style="flex: 1; min-width: 140px; padding: 1rem; background: var(--color-surface-subtle); border-radius: 2px; text-decoration: none;">
            <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: block;">Processing</span>
            <strong style="font-size: 1.25rem; font-weight: 600; color: #15803D;">{{ $statusCounts['processing'] ?? 0 }}</strong>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" style="flex: 1; min-width: 140px; padding: 1rem; background: var(--color-surface-subtle); border-radius: 2px; text-decoration: none;">
            <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: block;">Shipped</span>
            <strong style="font-size: 1.25rem; font-weight: 600; color: #1E40AF;">{{ $statusCounts['shipped'] ?? 0 }}</strong>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" style="flex: 1; min-width: 140px; padding: 1rem; background: var(--color-surface-subtle); border-radius: 2px; text-decoration: none;">
            <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: block;">Delivered</span>
            <strong style="font-size: 1.25rem; font-weight: 600; color: #15803D;">{{ $statusCounts['delivered'] ?? 0 }}</strong>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" style="flex: 1; min-width: 140px; padding: 1rem; background: var(--color-surface-subtle); border-radius: 2px; text-decoration: none;">
            <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: block;">Cancelled</span>
            <strong style="font-size: 1.25rem; font-weight: 600; color: #991B1B;">{{ $statusCounts['cancelled'] ?? 0 }}</strong>
        </a>
    </div>
</div>

<!-- Recent Orders Section -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.5rem 1.75rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: baseline;">
        <h3 class="serif" style="font-size: 1.25rem; font-weight: 500;">
            Recent Customer Orders
        </h3>
        <a href="{{ route('admin.orders.index') }}" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); text-decoration: underline;">
            View All Orders &rarr;
        </a>
    </div>

    @if($recentOrders->count() > 0)
        <div class="admin-table-wrap" style="border: none;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th>Shipment</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                        <tr>
                            <td>
                                <strong style="font-family: monospace; font-size: 0.8125rem;">{{ $order->order_number }}</strong>
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
                                <span class="status-badge {{ $pBadge }}">{{ ucfirst($pStatus) }}</span>
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
                                <span class="status-badge {{ $oBadge }}">{{ ucfirst($oStatus) }}</span>
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
                                <a href="{{ route('admin.orders.show', $order->order_number) }}" class="btn-editorial btn-editorial-outline" style="padding: 0.375rem 0.75rem; font-size: 0.6875rem;">
                                    Manage &rarr;
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="padding: 3rem; text-align: center; color: var(--color-text-muted); font-size: 0.875rem;">
            No orders found yet.
        </div>
    @endif
</div>
@endsection
