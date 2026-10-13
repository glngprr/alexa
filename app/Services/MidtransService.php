<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected string $serverKey;
    protected string $clientKey;
    protected bool $isProduction;
    protected string $snapUrl;
    protected string $apiUrl;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key') ?? '';
        $this->clientKey = config('services.midtrans.client_key') ?? '';
        $this->isProduction = (bool) config('services.midtrans.is_production', false);
        $this->snapUrl = config('services.midtrans.snap_url');
        $this->apiUrl = config('services.midtrans.api_url');
    }

    /**
     * Get the client key for Snap.js frontend integration if needed.
     */
    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    /**
     * Determine if Midtrans credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->serverKey);
    }

    /**
     * Create a Snap payment token and redirect URL for an order.
     *
     * @throws Exception
     */
    public function createSnapTransaction(Order $order, Payment $payment): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Midtrans server key is not configured in the environment.');
        }

        $order->loadMissing(['orderItems.productVariant.product', 'shippingAddress', 'user']);

        // Item details snapshot
        $itemDetails = [];
        foreach ($order->orderItems as $item) {
            $itemDetails[] = [
                'id' => substr((string) ($item->sku ?? $item->product_variant_id), 0, 50),
                'price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'name' => substr("{$item->product_name} - {$item->variant_name}", 0, 50),
            ];
        }

        if ($order->shipping_cost > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Shipping Fee',
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->shippingAddress?->recipient_name ?? $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->shippingAddress?->phone,
                'shipping_address' => [
                    'first_name' => $order->shippingAddress?->recipient_name ?? $order->user->name,
                    'phone' => $order->shippingAddress?->phone,
                    'address' => $order->shippingAddress?->address,
                    'city' => $order->shippingAddress?->city,
                    'postal_code' => $order->shippingAddress?->postal_code,
                ],
            ],
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => route('orders.show', $order->order_number),
            ],
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
        ])->post($this->snapUrl, $payload);

        if (!$response->successful()) {
            Log::error('Midtrans Snap Error', [
                'order_number' => $order->order_number,
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ]);

            $errorMessage = $response->json('error_messages.0') ?? 'Failed to initialize payment gateway.';
            throw new Exception("Payment gateway error: {$errorMessage}");
        }

        $data = $response->json();

        return [
            'token' => $data['token'] ?? null,
            'redirect_url' => $data['redirect_url'] ?? null,
        ];
    }

    /**
     * Query Midtrans Status API for an order status.
     *
     * @throws Exception
     */
    public function getTransactionStatus(string $orderId): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Midtrans server key is not configured.');
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
        ])->get("{$this->apiUrl}/{$orderId}/status");

        if (!$response->successful()) {
            Log::warning('Midtrans Status Query Error', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ]);

            throw new Exception("Unable to fetch status from Midtrans: HTTP {$response->status()}");
        }

        return $response->json() ?? [];
    }

    /**
     * Verify Midtrans notification signature key.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        if (empty($this->serverKey)) {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Map Midtrans transaction status to internal payment status.
     */
    public function mapStatus(string $transactionStatus, ?string $fraudStatus = null): string
    {
        return match ($transactionStatus) {
            'capture' => ($fraudStatus === 'challenge') ? 'pending' : 'paid',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'expired',
            'cancel' => 'cancelled',
            default => 'pending',
        };
    }
}
