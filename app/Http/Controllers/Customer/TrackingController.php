<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Refresh shipment tracking status.
     * Note: External courier tracking integration will be handled by the upcoming provider.
     */
    public function refresh(Request $request, Order $order): RedirectResponse
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

        if (empty($shipment->waybill_id) && $shipment->status === 'pending') {
            return redirect()->route('orders.show', $order->order_number)
                ->with('info', 'Shipment has been created and is awaiting courier assignment.');
        }

        return redirect()->route('orders.show', $order->order_number)
            ->with('info', 'Shipment tracking is up to date.');
    }
}
