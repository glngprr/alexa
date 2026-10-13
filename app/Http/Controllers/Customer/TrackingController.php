<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BiteshipService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    /**
     * Refresh shipment tracking status from Biteship API Sandbox.
     */
    public function refresh(Request $request, Order $order, BiteshipService $biteshipService): RedirectResponse
    {
        // 1. Authorize customer ownership or admin
        if ($order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403, 'Unauthorized access to this order.');
        }

        $shipment = $order->shipment;

        if (!$shipment) {
            return redirect()->route('orders.show', $order->order_number)
                ->with('error', 'Shipment has not been created for this order yet.');
        }

        // Use tracking ID or waybill or Biteship order ID
        $trackingIdentifier = $shipment->biteship_tracking_id ?? $shipment->waybill_id ?? $shipment->biteship_order_id;

        if (!$trackingIdentifier) {
            return redirect()->route('orders.show', $order->order_number)
                ->with('error', 'Tracking identifier is unavailable for this shipment.');
        }

        try {
            // If tracking_id or waybill_id is available, query Biteship tracking API.
            // Otherwise, query Biteship order API using biteship_order_id.
            if (!empty($shipment->biteship_tracking_id) || !empty($shipment->waybill_id)) {
                $identifier = $shipment->biteship_tracking_id ?: $shipment->waybill_id;
                $trackingData = $biteshipService->getTracking($identifier, $shipment->status);
            } elseif (!empty($shipment->biteship_order_id)) {
                $trackingData = $biteshipService->getOrder($shipment->biteship_order_id, $shipment->status);
            } else {
                return redirect()->route('orders.show', $order->order_number)
                    ->with('error', 'Tracking identifier is unavailable for this shipment.');
            }

            DB::transaction(function () use ($order, $shipment, $trackingData) {
                $incomingStatus = $trackingData['status'];

                // Ensure non-regression of status
                $finalStatus = \App\Models\Shipment::isValidStatusTransition($shipment->status, $incomingStatus)
                    ? $incomingStatus
                    : $shipment->status;

                $updateData = [
                    'status' => $finalStatus,
                    'biteship_tracking_id' => $trackingData['biteship_tracking_id'] ?? $shipment->biteship_tracking_id,
                    'waybill_id' => $trackingData['waybill_id'] ?? $shipment->waybill_id,
                    'courier_code' => $trackingData['courier_code'] ?? $shipment->courier_code,
                    'courier_service' => $trackingData['courier_service'] ?? $shipment->courier_service,
                    'courier_name' => $trackingData['courier_name'] ?? $shipment->courier_name,
                    'tracking_url' => $trackingData['tracking_url'] ?? $shipment->tracking_url,
                    'history' => !empty($trackingData['history']) ? $trackingData['history'] : $shipment->history,
                    'payload' => $trackingData['payload'] ?? $shipment->payload,
                ];

                // Update shipped_at timestamp only if status is shipped or in_transit and not set yet
                if (in_array($finalStatus, ['shipped', 'in_transit']) && !$shipment->shipped_at) {
                    $updateData['shipped_at'] = now();
                }

                // Update delivered_at timestamp only when delivered status is reached and not set yet
                if ($finalStatus === 'delivered' && !$shipment->delivered_at) {
                    $updateData['delivered_at'] = now();
                }

                // Order lifecycle transition (valid forward transitions only)
                if (in_array($finalStatus, ['shipped', 'in_transit']) && $order->order_status === 'processing') {
                    $order->update(['order_status' => 'shipped']);
                } elseif ($finalStatus === 'delivered' && in_array($order->order_status, ['processing', 'shipped'])) {
                    $order->update(['order_status' => 'delivered']);
                }

                $shipment->update($updateData);
            });

            if (empty($shipment->refresh()->waybill_id) && $shipment->status === 'pending') {
                return redirect()->route('orders.show', $order->order_number)
                    ->with('info', 'Shipment has been created and is awaiting courier assignment. Tracking information will be available once the courier generates a tracking number.');
            }

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Shipment tracking status updated successfully.');
        } catch (Exception $e) {
            Log::warning('Shipment Tracking Refresh Error', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('orders.show', $order->order_number)
                ->with('error', 'Unable to retrieve tracking update from Biteship: ' . $e->getMessage());
        }
    }
}
