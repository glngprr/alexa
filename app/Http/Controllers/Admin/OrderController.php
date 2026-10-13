<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of orders with filters and search.
     */
    public function index(Request $request): View
    {
        $query = Order::with(['user', 'payment', 'shipment'])->latest();

        // Search by order number, customer name, email, or recipient
        if ($request->filled('q')) {
            $search = trim($request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('shippingAddress', function ($aq) use ($search) {
                        $aq->where('recipient_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by order status
        if ($request->filled('status')) {
            $query->where('order_status', $request->input('status'));
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $pStatus = $request->input('payment_status');
            $query->whereHas('payment', function ($pq) use ($pStatus) {
                $pq->where('status', $pStatus);
            });
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display the specified order details for administration.
     */
    public function show(Order $order): View
    {
        $order->loadMissing([
            'user',
            'shippingAddress',
            'orderItems.productVariant.product',
            'payment',
            'payments' => fn ($q) => $q->latest(),
            'shipment',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the order status with strict lifecycle validation.
     * Note: Payment status is NEVER manipulated here and remains solely controlled by Midtrans.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_status' => ['required', 'string', 'in:pending,processing,packed,shipped,delivered,cancelled'],
        ]);

        $currentStatus = $order->order_status;
        $newStatus = $validated['order_status'];

        if ($currentStatus === $newStatus) {
            return back()->with('info', "Order status is already {$newStatus}.");
        }

        // Rule: Delivered is terminal, cannot regress
        if ($currentStatus === 'delivered' && $newStatus !== 'delivered') {
            return back()->with('error', 'Cannot change status of an already delivered order.');
        }

        // Rule: Cancelled is terminal, cannot regress
        if ($currentStatus === 'cancelled' && $newStatus !== 'cancelled') {
            return back()->with('error', 'Cannot reactivate a cancelled order.');
        }

        // Rule: If shipment is already delivered, order cannot be demoted
        if ($order->shipment && $order->shipment->isDelivered() && $newStatus !== 'delivered') {
            return back()->with('error', 'Shipment has already been delivered to customer. Order status cannot be reverted.');
        }

        // Rule: Do not change order to shipped or delivered if unpaid unless cancelled
        if ($order->paymentStatus() !== 'paid' && in_array($newStatus, ['processing', 'packed', 'shipped', 'delivered'])) {
            return back()->with('error', 'Order cannot advance to fulfillment while payment is unpaid.');
        }

        $order->update([
            'order_status' => $newStatus,
        ]);

        return redirect()->route('admin.orders.show', $order->order_number)
            ->with('success', "Order status updated to '" . ucfirst($newStatus) . "'.");
    }
}
