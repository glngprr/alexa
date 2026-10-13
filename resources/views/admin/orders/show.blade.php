@extends('layouts.admin')

@section('title', 'Manage Order ' . $order->order_number . ' — Alexa Admin')

@section('content')
<div class="page-header">
    <div>
        <a href="{{ route('admin.orders.index') }}" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: inline-block; margin-bottom: 0.5rem;">
            &larr; Back to Orders List
        </a>
        <h1 class="page-title">{{ $order->order_number }}</h1>
        <p class="page-subtitle">Placed on {{ $order->created_at->format('F d, Y \a\t H:i') }} &bull; Customer: <strong>{{ $order->user->name ?? 'Guest' }}</strong></p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        @php
            $oStatus = $order->order_status;
            $oBadge = match($oStatus) {
                'processing' => 'status-badge-processing',
                'shipped', 'packed' => 'status-badge-shipped',
                'delivered' => 'status-badge-delivered',
                'cancelled' => 'status-badge-cancelled',
                default => 'status-badge-pending',
            };

            $pStatus = $order->paymentStatus();
            $pBadge = match($pStatus) {
                'paid' => 'status-badge-paid',
                'failed' => 'status-badge-failed',
                'expired' => 'status-badge-expired',
                'cancelled' => 'status-badge-cancelled',
                default => 'status-badge-pending',
            };
        @endphp
        <span class="status-badge {{ $oBadge }}" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;">
            Order: {{ ucfirst($oStatus) }}
        </span>
        <span class="status-badge {{ $pBadge }}" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;">
            Payment: {{ ucfirst($pStatus) }}
        </span>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1.1fr; gap: 2rem; align-items: flex-start;">
    <!-- Left Column: Items, Destination & History -->
    <div>
        <!-- Purchased Items Card -->
        <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 2rem;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between;">
                <h3 class="serif" style="font-size: 1.125rem; font-weight: 500;">
                    Items in Order ({{ $order->orderItems->count() }})
                </h3>
                <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-subtle);">
                    Historical Snapshot
                </span>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Item</th>
                        <th>Product &amp; Color</th>
                        <th>SKU</th>
                        <th style="text-align: right;">Unit Price</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->orderItems as $item)
                        @php
                            $variantImg = $item->productVariant?->image ? asset($item->productVariant->image) : asset('images/products/core-midnight.png');
                        @endphp
                        <tr>
                            <td>
                                <img src="{{ $variantImg }}" alt="{{ $item->product_name }}" style="width: 40px; height: 40px; object-fit: cover; background: #F4F4F0; border: 1px solid var(--color-border); border-radius: 2px;">
                            </td>
                            <td>
                                <strong style="font-size: 0.875rem; display: block;">{{ $item->product_name }}</strong>
                                <span style="font-size: 0.75rem; color: var(--color-text-muted);">{{ $item->variant_name }}</span>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 0.75rem; color: var(--color-text-muted);">{{ $item->sku }}</span>
                            </td>
                            <td style="text-align: right; color: var(--color-text-muted);">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center; font-weight: 600;">
                                {{ $item->quantity }}
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: 500; color: var(--color-text-muted);">Subtotal</td>
                        <td style="text-align: right; font-weight: 600;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: 500; color: var(--color-text-muted);">Shipping Delivery</td>
                        <td style="text-align: right; font-weight: 600; color: #15803D;">Complimentary (Rp 0)</td>
                    </tr>
                    <tr style="background: var(--color-surface-subtle);">
                        <td colspan="5" style="text-align: right; font-weight: 600; font-size: 0.9375rem;">Total Amount</td>
                        <td style="text-align: right; font-weight: 700; font-size: 1.0625rem;">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Shipping Destination Card -->
        <div class="card" style="margin-bottom: 2rem;">
            <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1rem;">
                Delivery Destination
            </h3>
            @if($order->shippingAddress)
                <div style="font-size: 0.875rem; line-height: 1.8;">
                    <div style="font-weight: 600; font-size: 0.9375rem;">{{ $order->shippingAddress->recipient_name }}</div>
                    <div style="color: var(--color-text-muted);">Phone: {{ $order->shippingAddress->phone }}</div>
                    <div>{{ $order->shippingAddress->address }}</div>
                    <div>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->province }} {{ $order->shippingAddress->postal_code }}</div>
                </div>
            @else
                <p style="font-size: 0.875rem; color: var(--color-text-muted);">Shipping address not recorded.</p>
            @endif
        </div>

        <!-- Shipment & Biteship Monitoring Card -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1.25rem;">
                <h3 class="serif" style="font-size: 1.125rem; font-weight: 500;">
                    Shipment &amp; Tracking (Biteship Sandbox)
                </h3>
                @if($order->shipment)
                    <span class="status-badge {{ $order->shipment->isDelivered() ? 'status-badge-delivered' : 'status-badge-shipped' }}">
                        {{ ucfirst(str_replace('_', ' ', $order->shipment->status)) }}
                    </span>
                @else
                    <span class="status-badge status-badge-pending">Unallocated</span>
                @endif
            </div>

            @if($order->shipment)
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; background: var(--color-surface-subtle); padding: 1.25rem; border: 1px solid var(--color-border); font-size: 0.8125rem;">
                    <div>
                        <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: block;">Courier Partner</span>
                        <strong>{{ $order->shipment->courier_name ?? strtoupper($order->shipment->courier_code) }}</strong> ({{ strtoupper($order->shipment->courier_service) }})
                    </div>
                    <div>
                        <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: block;">Waybill (AWB)</span>
                        <strong style="font-family: monospace;">{{ $order->shipment->waybill_id ?: 'Awaiting Courier Assignment' }}</strong>
                    </div>
                    <div>
                        <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: block;">Biteship Order ID</span>
                        <code style="font-size: 0.75rem;">{{ $order->shipment->biteship_order_id }}</code>
                    </div>
                    <div>
                        <span style="font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: block;">Tracking URL</span>
                        @if($order->shipment->tracking_url)
                            <a href="{{ $order->shipment->tracking_url }}" target="_blank" rel="noopener" style="font-size: 0.75rem; text-decoration: underline; color: #1E40AF;">
                                View on Courier Portal &nearr;
                            </a>
                        @else
                            <span style="font-size: 0.75rem; color: var(--color-text-subtle);">None</span>
                        @endif
                    </div>
                </div>

                @if(!empty($order->shipment->history))
                    <div style="margin-top: 1.25rem;">
                        <h4 style="font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); margin-bottom: 0.75rem;">
                            Courier Event Log
                        </h4>
                        <div style="border-left: 2px solid var(--color-border); padding-left: 1rem;">
                            @foreach($order->shipment->history as $ev)
                                <div style="margin-bottom: 0.75rem; font-size: 0.8125rem;">
                                    <div style="font-weight: 500;">{{ $ev['note'] ?? $ev['status'] ?? 'Update' }}</div>
                                    @if(!empty($ev['updated_at']))
                                        <div style="font-size: 0.6875rem; color: var(--color-text-subtle);">{{ $ev['updated_at'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <p style="font-size: 0.875rem; color: var(--color-text-muted); margin-bottom: 1.25rem; line-height: 1.6;">
                    No courier shipment has been generated for this order yet.
                </p>

                @if($order->canBeShipped())
                    <form action="{{ route('admin.orders.shipment.store', $order->order_number) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-editorial btn-editorial-primary" id="btn-admin-fulfill-order">
                            Dispatch Shipment (Biteship Sandbox) &rarr;
                        </button>
                    </form>
                @elseif($order->paymentStatus() !== 'paid')
                    <p style="font-size: 0.8125rem; color: #991B1B;">
                        &bull; Shipment creation locked until payment is verified as <strong>Paid</strong>.
                    </p>
                @endif
            @endif
        </div>
    </div>

    <!-- Right Column: Status Controls & Payment Settlement -->
    <div>
        <!-- Order Lifecycle Control Card -->
        <div class="card" style="margin-bottom: 1.75rem;">
            <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1.25rem;">
                Order Status Control
            </h3>

            <form action="{{ route('admin.orders.status.update', $order->order_number) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label class="form-label" for="order_status">Current Order Status</label>
                    <select name="order_status" id="order_status" class="form-select" {{ in_array($order->order_status, ['delivered', 'cancelled']) ? 'disabled' : '' }}>
                        <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending (Unpaid)</option>
                        <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing (Paid)</option>
                        <option value="packed" {{ $order->order_status === 'packed' ? 'selected' : '' }}>Packed</option>
                        <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ $order->order_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                @if(!in_array($order->order_status, ['delivered', 'cancelled']))
                    <button type="submit" class="btn-editorial btn-editorial-primary" style="width: 100%;" id="btn-update-order-status">
                        Update Order Status
                    </button>
                @else
                    <p style="font-size: 0.75rem; color: var(--color-text-subtle); margin-top: 0.5rem;">
                        This order has reached its terminal state ({{ ucfirst($order->order_status) }}) and cannot regress.
                    </p>
                @endif
            </form>

            <div style="margin-top: 1rem; padding: 0.75rem; background: var(--color-surface-subtle); border-radius: 2px; font-size: 0.6875rem; color: var(--color-text-muted); line-height: 1.5;">
                &bull; <strong>Security Policy:</strong> Payment status cannot be altered manually by administrators and is solely governed by verified Midtrans payment settlements.
            </div>
        </div>

        <!-- Payment Settlement Monitoring Card -->
        <div class="card">
            <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1.25rem;">
                Payment Settlement (Midtrans)
            </h3>

            <div style="font-size: 0.8125rem; line-height: 1.9;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-bottom: 0.5rem;">
                    <span style="color: var(--color-text-muted);">Payment Gateway</span>
                    <strong>Midtrans Snap Sandbox</strong>
                </div>

                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-bottom: 0.5rem;">
                    <span style="color: var(--color-text-muted);">Settlement Status</span>
                    <span class="status-badge {{ $pBadge }}" id="payment-status-badge">
                        {{ ucfirst($order->paymentStatus()) }}
                    </span>
                </div>

                @if($order->payment)
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-bottom: 0.5rem;">
                        <span style="color: var(--color-text-muted);">Payment Method</span>
                        <strong>{{ ucfirst(str_replace('_', ' ', $order->payment->payment_method ?? 'Not Selected')) }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-bottom: 0.5rem;">
                        <span style="color: var(--color-text-muted);">Transaction ID</span>
                        <code style="font-size: 0.75rem;">{{ $order->payment->transaction_id ?? '-' }}</code>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-bottom: 0.5rem;">
                        <span style="color: var(--color-text-muted);">Settled Amount</span>
                        <strong>Rp {{ number_format($order->payment->amount, 0, ',', '.') }}</strong>
                    </div>

                    @if($order->payment->paid_at)
                        <div style="display: flex; justify-content: space-between; padding-bottom: 0.5rem;">
                            <span style="color: var(--color-text-muted);">Paid At</span>
                            <span>{{ $order->payment->paid_at->format('M d, Y H:i:s') }}</span>
                        </div>
                    @endif
                @else
                    <p style="color: var(--color-text-muted); font-size: 0.75rem;">No payment transaction initialized yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
