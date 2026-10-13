<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Initiate Midtrans Snap payment for an order.
     */
    public function pay(Request $request, Order $order, MidtransService $midtransService): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to this order.');
        }

        if (!$order->isEligibleForPayment()) {
            return redirect()->route('orders.show', $order->order_number)
                ->with('error', 'This order is not eligible for payment or has already been paid.');
        }

        try {
            $payment = DB::transaction(function () use ($order) {
                $existingPayment = $order->payments()->where('status', 'pending')->latest('id')->first();

                if ($existingPayment) {
                    return $existingPayment;
                }

                return $order->payments()->create([
                    'payment_gateway' => 'midtrans',
                    'amount' => $order->total,
                    'status' => 'pending',
                ]);
            });

            // If we already have a valid redirect url, reuse it or fetch new one
            if ($payment->snap_redirect_url) {
                return redirect()->away($payment->snap_redirect_url);
            }

            $snapResult = $midtransService->createSnapTransaction($order, $payment);

            $payment->update([
                'snap_token' => $snapResult['token'],
                'snap_redirect_url' => $snapResult['redirect_url'],
            ]);

            if ($payment->snap_redirect_url) {
                return redirect()->away($payment->snap_redirect_url);
            }

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Payment session initialized. Please complete payment.');
        } catch (Exception $e) {
            Log::error('Payment Initiation Failed', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('orders.show', $order->order_number)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Refresh payment status by querying Midtrans API directly.
     */
    public function checkStatus(Request $request, Order $order, MidtransService $midtransService): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to this order.');
        }

        try {
            $statusData = $midtransService->getTransactionStatus($order->order_number);

            if (empty($statusData) || !isset($statusData['transaction_status'])) {
                return redirect()->route('orders.show', $order->order_number)
                    ->with('info', 'Payment status is currently pending.');
            }

            $mappedStatus = $midtransService->mapStatus(
                $statusData['transaction_status'],
                $statusData['fraud_status'] ?? null
            );

            DB::transaction(function () use ($order, $statusData, $mappedStatus) {
                $payment = $order->payment ?? $order->payments()->create([
                    'payment_gateway' => 'midtrans',
                    'amount' => $order->total,
                    'status' => 'pending',
                ]);

                $updateData = [
                    'status' => $mappedStatus,
                    'payment_method' => $statusData['payment_type'] ?? $payment->payment_method,
                    'transaction_id' => $statusData['transaction_id'] ?? $payment->transaction_id,
                    'payload' => $statusData,
                ];

                if ($mappedStatus === 'paid' && !$payment->paid_at) {
                    $updateData['paid_at'] = now();
                    $order->update(['order_status' => 'processing']);
                }

                $payment->update($updateData);
            });

            return redirect()->route('orders.show', $order->order_number)
                ->with('success', 'Payment status updated successfully.');
        } catch (Exception $e) {
            Log::warning('Payment Status Check Error', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('orders.show', $order->order_number)
                ->with('error', 'Unable to refresh payment status from Midtrans: ' . $e->getMessage());
        }
    }
}
