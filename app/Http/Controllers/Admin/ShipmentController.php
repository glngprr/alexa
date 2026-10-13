<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\BiteshipService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShipmentController extends Controller
{
    /**
     * Create a new shipment for a paid order via Biteship Sandbox.
     */
    public function store(Request $request, Order $order, BiteshipService $biteshipService): RedirectResponse
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

        try {
            // Call external Biteship API before any DB transactions
            $result = $biteshipService->createOrder($order);

            // Store shipment record in DB transaction
            DB::transaction(function () use ($order, $result) {
                $order->shipments()->create([
                    'provider' => 'biteship',
                    'biteship_order_id' => $result['biteship_order_id'],
                    'biteship_tracking_id' => $result['biteship_tracking_id'],
                    'waybill_id' => $result['waybill_id'],
                    'courier_code' => $result['courier_code'],
                    'courier_service' => $result['courier_service'],
                    'courier_name' => $result['courier_name'],
                    'status' => $result['status'],
                    'tracking_url' => $result['tracking_url'],
                    'shipped_at' => in_array($result['status'], ['shipped', 'in_transit']) ? now() : null,
                    'payload' => $result['payload'],
                ]);

                if (in_array($result['status'], ['shipped', 'in_transit']) && $order->order_status === 'processing') {
                    $order->update(['order_status' => 'shipped']);
                }
            });

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Shipment successfully created via Biteship Sandbox.');
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
