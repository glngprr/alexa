@extends('layouts.app')

@section('title', 'Order ' . $order->order_number . ' — Alexa')
@section('meta_description', 'Order confirmation details for ' . $order->order_number)

@push('styles')
<style>
    .order-confirmation-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 3.5rem;
        align-items: flex-start;
    }

    @media (min-width: 1024px) {
        .order-confirmation-layout {
            grid-template-columns: 1.6fr 1fr;
            gap: 4.5rem;
        }
    }

    .order-box {
        background: #FFFFFF;
        border: 1px solid var(--color-border);
        padding: 2.5rem 2rem;
        margin-bottom: 2rem;
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

    .order-items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        font-size: 0.875rem;
    }

    .order-items-table th {
        text-align: left;
        padding: 0.875rem 0;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--color-text-subtle);
        font-weight: 500;
        border-bottom: 1px solid var(--color-border);
    }

    .order-items-table td {
        padding: 1.25rem 0;
        border-bottom: 1px solid var(--color-border);
        vertical-align: top;
    }

    .alert-banner {
        padding: 1rem 1.25rem;
        font-size: 0.875rem;
        line-height: 1.6;
        margin-bottom: 2rem;
        border: 1px solid;
    }

    .alert-banner-success {
        background-color: #F0FDF4;
        border-color: #BBF7D0;
        color: #15803D;
    }

    .alert-banner-error {
        background-color: #FEF2F2;
        border-color: #FECACA;
        color: #991B1B;
    }

    .alert-banner-info {
        background-color: #EFF6FF;
        border-color: #BFDBFE;
        color: #1E40AF;
    }

    .tracking-timeline {
        margin-top: 1.25rem;
        padding-left: 1.25rem;
        border-left: 2px solid var(--color-border);
    }

    .tracking-event {
        position: relative;
        margin-bottom: 1.25rem;
    }

    .tracking-event:last-child {
        margin-bottom: 0;
    }

    .tracking-dot {
        position: absolute;
        left: -1.625rem;
        top: 0.25rem;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: var(--color-primary);
    }
</style>
@endpush

@section('content')
<div class="container-custom" style="padding-top: 2.5rem; padding-bottom: 7rem;">
    @if(session('success'))
        <div class="alert-banner alert-banner-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-banner alert-banner-error">
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert-banner alert-banner-info">
            {{ session('info') }}
        </div>
    @endif

    <!-- Confirmation Banner -->
    <div style="margin-bottom: 3.5rem; text-align: left;">
        <span class="eyebrow" style="color: {{ $order->paymentStatus() === 'paid' ? '#15803D' : '#8F6B00' }};">
            {{ $order->paymentStatus() === 'paid' ? 'Payment Confirmed' : 'Order Recorded' }}
        </span>
        <h1 class="serif" style="font-size: clamp(2.25rem, 4vw, 3.25rem); font-weight: 400; letter-spacing: -0.01em; margin-bottom: 0.75rem;">
            {{ $order->paymentStatus() === 'paid' ? 'Order Confirmed' : 'Thank you for your order.' }}
        </h1>
        <p style="font-size: 1rem; color: var(--color-text-muted); max-width: 600px; line-height: 1.7;">
            @if($order->paymentStatus() === 'paid')
                Your payment was received successfully. We are now preparing your order for fulfillment.
            @else
                Your order has been placed successfully. Please complete your payment via Midtrans Sandbox below to proceed with fulfillment.
            @endif
        </p>
    </div>

    <div class="order-confirmation-layout">
        <!-- Left Column: Order Items & Delivery Details -->
        <div>
            <!-- Order Items Box -->
            <div class="order-box">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                    <h2 class="serif" style="font-size: 1.375rem; font-weight: 400;">
                        Ordered Items
                    </h2>
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-subtle);">
                        {{ $order->orderItems->count() }} {{ Str::plural('Item', $order->orderItems->count()) }}
                    </span>
                </div>

                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Product Details</th>
                            <th style="text-align: center; width: 15%;">Qty</th>
                            <th style="text-align: right; width: 17%;">Price</th>
                            <th style="text-align: right; width: 18%;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->orderItems as $item)
                            <tr>
                                <td>
                                    <h4 class="serif" style="font-size: 1rem; font-weight: 400; margin-bottom: 0.25rem;">
                                        {{ $item->product_name }}
                                    </h4>
                                    <span style="font-size: 0.75rem; color: var(--color-text-muted); display: block;">
                                        Color: {{ $item->variant_name }} &bull; SKU: {{ $item->sku }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 500;">
                                    {{ $item->quantity }}
                                </td>
                                <td style="text-align: right; color: var(--color-text-muted);">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 500; color: var(--color-text-main);">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Shipping Destination Details Box -->
            <div class="order-box">
                <h2 class="serif" style="font-size: 1.375rem; font-weight: 400; margin-bottom: 1.25rem;">
                    Shipping Destination
                </h2>

                @if($order->shippingAddress)
                    <div style="font-size: 0.875rem; line-height: 1.8; color: var(--color-text-main);">
                        <p style="font-weight: 600;">{{ $order->shippingAddress->recipient_name }}</p>
                        <p style="color: var(--color-text-muted);">Phone: {{ $order->shippingAddress->phone }}</p>
                        <p>{{ $order->shippingAddress->address }}</p>
                        <p>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->province }} {{ $order->shippingAddress->postal_code }}</p>
                    </div>
                @else
                    <p style="font-size: 0.875rem; color: var(--color-text-muted);">Address details unavailable.</p>
                @endif
            </div>

            <!-- Shipping & Tracking Details Box -->
            <div class="order-box">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                    <h2 class="serif" style="font-size: 1.375rem; font-weight: 400;">
                        Shipping &amp; Tracking
                    </h2>
                    @if($order->shipment)
                        @php
                            $shipmentStatus = strtolower($order->shipmentStatus());
                            $shipBadge = match($shipmentStatus) {
                                'delivered' => 'status-badge-delivered',
                                'in_transit' => 'status-badge-in_transit',
                                'shipped' => 'status-badge-shipped',
                                'cancelled' => 'status-badge-cancelled',
                                default => 'status-badge-pending',
                            };
                        @endphp
                        <span class="status-badge {{ $shipBadge }}" id="shipment-status-badge">{{ ucfirst(str_replace('_', ' ', $order->shipmentStatus())) }}</span>
                    @else
                        <span class="status-badge status-badge-pending" id="shipment-status-badge">Unfulfilled</span>
                    @endif
                </div>

                @if($order->shipment)
                    <div style="font-size: 0.875rem; line-height: 1.8; color: var(--color-text-main); margin-bottom: 1.5rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; background: var(--color-surface); padding: 1.25rem; border: 1px solid var(--color-border);">
                            <div>
                                <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: block;">Courier Partner</span>
                                <strong>{{ $order->shipment->courier_name ?? strtoupper($order->shipment->courier_code) }}</strong> ({{ strtoupper($order->shipment->courier_service) }})
                            </div>
                            <div>
                                <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); display: block;">Waybill / Tracking No. (AWB)</span>
                                <strong style="font-family: monospace; font-size: 0.9375rem;" id="tracking-waybill">{{ $order->shipment->waybill_id ?: 'Awaiting Courier Assignment' }}</strong>
                            </div>
                        </div>

                        @if(!empty($order->shipment->history))
                            <div style="margin-top: 1.5rem;">
                                <h4 class="serif" style="font-size: 1.125rem; font-weight: 400; margin-bottom: 1rem;">Tracking History</h4>
                                <div class="tracking-timeline">
                                    @foreach($order->shipment->history as $event)
                                        <div class="tracking-event">
                                            <div class="tracking-dot"></div>
                                            <div style="font-weight: 500; font-size: 0.875rem; color: var(--color-text-main);">
                                                {{ $event['note'] ?? $event['status'] ?? 'Update' }}
                                            </div>
                                            @if(!empty($event['updated_at']))
                                                <div style="font-size: 0.75rem; color: var(--color-text-muted);">
                                                    {{ \Carbon\Carbon::parse($event['updated_at'])->format('M d, Y H:i') }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div style="margin-top: 1rem; padding: 0.875rem 1rem; background: var(--color-surface); border: 1px dashed var(--color-border); font-size: 0.8125rem; color: var(--color-text-muted);">
                                &bull; Live tracking events will be displayed here as the courier logs package movement.
                            </div>
                        @endif

                        <div style="margin-top: 1.5rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                            <form action="{{ route('orders.shipment.track', $order->order_number) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-editorial btn-editorial-outline" style="padding: 0.5rem 1.25rem; font-size: 0.75rem;" id="btn-refresh-tracking">
                                    &circlearrowright; Refresh Tracking Status
                                </button>
                            </form>

                            @if($order->shipment->tracking_url)
                                <a href="{{ $order->shipment->tracking_url }}" target="_blank" rel="noopener noreferrer" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); text-decoration: underline;">
                                    Track on Courier Website &nearr;
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.7;">
                        Awaiting shipment creation and courier assignment by the Alexa logistics team.
                    </p>
                @endif

                {{-- Administrator Fulfillment Action Box --}}
                @if(auth()->user()?->isAdmin())
                    <div style="margin-top: 2rem; padding: 1.5rem; background: #FAF9F6; border: 1px solid #E5E4DE;">
                        <span class="eyebrow" style="color: #6B6B66; margin-bottom: 0.25rem;">Administrator Controls</span>
                        <h3 class="serif" style="font-size: 1.125rem; font-weight: 500; margin-bottom: 0.75rem;">Logistics Fulfillment</h3>

                        @if($order->canBeShipped())
                            <p style="font-size: 0.8125rem; color: var(--color-text-muted); margin-bottom: 1rem; line-height: 1.6;">
                                Payment is confirmed. You can now generate an automated courier shipment order via Biteship Sandbox.
                            </p>
                            <form action="{{ route('admin.orders.shipment.store', $order->order_number) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-editorial btn-editorial-primary" style="padding: 0.625rem 1.5rem; font-size: 0.75rem;" id="btn-admin-create-shipment">
                                    Create Courier Shipment (Biteship Sandbox) &rarr;
                                </button>
                            </form>
                        @elseif($order->paymentStatus() !== 'paid')
                            <p style="font-size: 0.8125rem; color: #991B1B; line-height: 1.5; margin: 0;">
                                &bull; Shipment creation is locked until payment status is confirmed as <strong>Paid</strong>.
                            </p>
                        @else
                            <p style="font-size: 0.8125rem; color: #15803D; line-height: 1.5; margin: 0;">
                                &check; Shipment has already been generated (Biteship Order: <code>{{ $order->shipment?->biteship_order_id }}</code>).
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div style="margin-top: 1rem;">
                <a href="{{ route('products.index') }}" class="btn-editorial btn-editorial-outline" id="btn-continue-shopping">
                    &larr; Continue Exploring Products
                </a>
            </div>
        </div>

        <!-- Right Column: Order Summary, Status & Payment Actions -->
        <div>
            <div style="background: var(--color-surface); border: 1px solid var(--color-border); padding: 2.25rem 2rem;">
                <h2 class="serif" style="font-size: 1.375rem; font-weight: 400; margin-bottom: 1.5rem; letter-spacing: -0.01em;">
                    Order Summary
                </h2>

                <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--color-border);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.875rem;">
                        <span style="color: var(--color-text-muted);">Order Number</span>
                        <strong style="color: var(--color-text-main); font-family: monospace; font-size: 0.875rem;">{{ $order->order_number }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.875rem;">
                        <span style="color: var(--color-text-muted);">Date Placed</span>
                        <span style="color: var(--color-text-main);">{{ $order->created_at->format('F d, Y') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; font-size: 0.875rem;">
                        <span style="color: var(--color-text-muted);">Order Status</span>
                        @php
                            $orderStatus = strtolower($order->order_status);
                            $badgeClass = match($orderStatus) {
                                'processing' => 'status-badge-processing',
                                'shipped' => 'status-badge-shipped',
                                'delivered' => 'status-badge-delivered',
                                'cancelled' => 'status-badge-cancelled',
                                default => 'status-badge-pending',
                            };
                        @endphp
                        <span class="status-badge {{ $badgeClass }}" id="order-status-badge">{{ ucfirst($order->order_status) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem;">
                        <span style="color: var(--color-text-muted);">Payment Status</span>
                        @php
                            $pStatus = $order->paymentStatus();
                            $pBadgeClass = match($pStatus) {
                                'paid' => 'status-badge-paid',
                                'failed' => 'status-badge-failed',
                                'expired' => 'status-badge-expired',
                                'cancelled' => 'status-badge-cancelled',
                                default => 'status-badge-pending',
                            };
                        @endphp
                        <span class="status-badge {{ $pBadgeClass }}" id="payment-status-badge">{{ ucfirst($pStatus) }}</span>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.875rem; font-size: 0.875rem; color: var(--color-text-muted);">
                    <span>Subtotal</span>
                    <span style="font-weight: 500; color: var(--color-text-main);">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.875rem; font-size: 0.875rem; color: var(--color-text-muted);">
                    <span>Shipping Delivery</span>
                    <span style="color: #2D5A27; font-weight: 500;">Complimentary (Rp 0)</span>
                </div>

                <div style="display: flex; justify-content: space-between; padding-top: 1.25rem; margin-top: 1rem; border-top: 1px solid var(--color-border); font-size: 1.125rem; font-weight: 500; color: var(--color-text-main);">
                    <span class="serif">Total Due</span>
                    <span class="serif" style="font-size: 1.375rem; font-weight: 600;">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>

                <!-- Payment Actions Area -->
                <div style="margin-top: 2rem;">
                    @if($order->isEligibleForPayment())
                        <form action="{{ route('orders.payment', $order->order_number) }}" method="POST" id="pay-form">
                            @csrf
                            <button type="submit" class="btn-editorial btn-editorial-primary" style="width: 100%; height: 3.25rem;" id="btn-pay-now">
                                Pay Now (Midtrans Sandbox) &rarr;
                            </button>
                        </form>

                        <div style="margin-top: 1rem; text-align: center;">
                            <form action="{{ route('orders.payment.status', $order->order_number) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" style="background: none; border: none; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-muted); cursor: pointer; text-decoration: underline;" id="btn-check-payment-status">
                                    Already Paid? Check Payment Status
                                </button>
                            </form>
                        </div>

                        <div style="margin-top: 1.25rem; padding: 1rem; background: #FFFFFF; border: 1px solid var(--color-border); font-size: 0.75rem; color: var(--color-text-muted); line-height: 1.6;">
                            <strong style="color: var(--color-text-main); display: block; margin-bottom: 0.25rem;">Midtrans Sandbox Notice</strong>
                            This store is configured with Midtrans Snap Sandbox. Simulated test cards, GoPay, and Virtual Accounts can be used to test transactions.
                        </div>
                    @elseif($order->paymentStatus() === 'paid')
                        <div style="padding: 1.25rem; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 2px;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <strong style="color: #15803D; font-size: 0.875rem;">Payment Completed</strong>
                            </div>
                            <p style="font-size: 0.75rem; color: #166534; line-height: 1.5; margin: 0;">
                                Method: {{ ucfirst($order->payment?->payment_method ?? 'Midtrans') }}<br>
                                Paid at: {{ $order->payment?->paid_at ? $order->payment->paid_at->format('F d, Y H:i') : $order->updated_at->format('F d, Y H:i') }}
                            </p>
                        </div>
                    @else
                        <div style="padding: 1rem; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 2px; font-size: 0.75rem; color: #991B1B;">
                            <strong>Payment Status: {{ ucfirst($order->paymentStatus()) }}</strong>
                            <p style="margin-top: 0.25rem; line-height: 1.5;">This payment could not be completed or has expired.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
