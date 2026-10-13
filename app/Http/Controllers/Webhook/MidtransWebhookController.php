<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    /**
     * Handle incoming Midtrans HTTP Notification (Webhook).
     */
    public function handle(Request $request, MidtransService $midtransService): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans Webhook Received', [
            'order_id' => $payload['order_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'status_code' => $payload['status_code'] ?? null,
        ]);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey || !$transactionStatus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incomplete webhook notification payload.',
            ], 400);
        }

        // Verify SHA512 signature
        if (!$midtransService->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans Webhook Signature Verification Failed', [
                'order_id' => $orderId,
                'signature_provided' => $signatureKey,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid signature key.',
            ], 400);
        }

        // Find Order
        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            Log::warning('Midtrans Webhook Order Not Found', ['order_id' => $orderId]);

            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.',
            ], 404);
        }

        // Verify amount
        $incomingAmount = (int) round((float) $grossAmount);
        if ($incomingAmount !== (int) $order->total) {
            Log::warning('Midtrans Webhook Amount Mismatch', [
                'order_number' => $order->order_number,
                'expected' => $order->total,
                'received' => $incomingAmount,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gross amount mismatch.',
            ], 400);
        }

        $mappedStatus = $midtransService->mapStatus($transactionStatus, $fraudStatus);

        try {
            DB::transaction(function () use ($order, $payload, $mappedStatus) {
                $payment = $order->payment ?? $order->payments()->create([
                    'payment_gateway' => 'midtrans',
                    'amount' => $order->total,
                    'status' => 'pending',
                ]);

                // Check for idempotent handling of already paid
                if ($payment->status === 'paid' && $mappedStatus === 'paid') {
                    return;
                }

                $updateData = [
                    'status' => $mappedStatus,
                    'payment_method' => $payload['payment_type'] ?? $payment->payment_method,
                    'transaction_id' => $payload['transaction_id'] ?? $payment->transaction_id,
                    'payload' => $payload,
                ];

                if ($mappedStatus === 'paid') {
                    $updateData['paid_at'] = $payment->paid_at ?? now();
                    $order->update(['order_status' => 'processing']);
                }

                $payment->update($updateData);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Notification processed successfully.',
            ], 200);
        } catch (Exception $e) {
            Log::error('Midtrans Webhook Processing Exception', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal processing error.',
            ], 500);
        }
    }
}
