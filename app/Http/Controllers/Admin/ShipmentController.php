<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShipmentController extends Controller
{
    /**
     * Create a new shipment for a paid order.
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        // 1. Verify Payment Status is 'paid' (NOT order_status = 'paid')
        if ($order->paymentStatus() !== 'paid') {
            return redirect()->route('orders.show', $order->order_number)
                ->with('error', 'Cannot create shipment for unpaid order.');
        }

        // 2. Prevent duplicate shipment creation
        if (!$order->canBeShipped()) {
            return redirect()->route('orders.show', $order->order_number)
                ->with('error', 'This order already has an active shipment.');
        }

        $validated = $request->validate([
            'courier_code' => ['nullable', 'string', 'max:50'],
            'courier_name' => ['nullable', 'string', 'max:100'],
            'courier_service' => ['nullable', 'string', 'max:50'],
            'waybill_id' => ['nullable', 'string', 'max:100'],
            'tracking_url' => ['nullable', 'url', 'max:255'],
        ]);

        try {
            // Store shipment record in DB transaction
            DB::transaction(function () use ($order, $validated) {
                $status = !empty($validated['waybill_id']) ? 'shipped' : 'pending';

                $order->shipments()->create([
                    'provider' => 'manual',
                    'waybill_id' => $validated['waybill_id'] ?? null,
                    'courier_code' => $validated['courier_code'] ?? 'jne',
                    'courier_service' => $validated['courier_service'] ?? 'reg',
                    'courier_name' => $validated['courier_name'] ?? 'JNE Reguler',
                    'status' => $status,
                    'tracking_url' => $validated['tracking_url'] ?? null,
                    'shipped_at' => $status === 'shipped' ? now() : null,
                ]);

                if ($status === 'shipped' && $order->order_status === 'processing') {
                    $order->update(['order_status' => 'shipped']);
                }
            });

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Shipment successfully created.');
        } catch (Exception $e) {
            Log::error('Admin Shipment Creation Error', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('orders.show', $order->order_number)
                ->with('error', 'Failed to create shipment: ' . $e->getMessage());
        }
    }
}
