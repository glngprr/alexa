<?php

namespace App\Services;

use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiteshipService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $courierCompany;
    protected string $courierType;
    protected array $origin;

    public function __construct()
    {
        $this->apiKey = (string) config('services.biteship.api_key', '');
        $this->baseUrl = rtrim((string) config('services.biteship.base_url', 'https://api.biteship.com'), '/');
        $this->courierCompany = (string) config('services.biteship.courier_company', 'jne');
        $this->courierType = (string) config('services.biteship.courier_type', 'reg');
        $this->origin = (array) config('services.biteship.origin', []);
    }

    /**
     * Check if Biteship credentials and origin configuration are present.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey)
            && !empty($this->origin['contact_name'] ?? '')
            && !empty($this->origin['contact_phone'] ?? '')
            && !empty($this->origin['address'] ?? '')
            && !empty($this->origin['postal_code'] ?? '');
    }

    /**
     * Create shipment order via Biteship Order API Sandbox.
     *
     * @throws Exception
     */
    public function createOrder(Order $order, array $courierOptions = []): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Biteship configuration or origin shipping details are incomplete in environment.');
        }

        $order->loadMissing(['shippingAddress', 'orderItems.productVariant.product', 'user']);

        if (!$order->shippingAddress) {
            throw new Exception('Order does not have a valid shipping destination address.');
        }

        $items = [];
        foreach ($order->orderItems as $item) {
            // Estimate weight in grams from product specification or default to 500g
            $weightInGrams = 500;
            if ($item->productVariant?->product?->weight) {
                if (preg_match('/(\d+)/', $item->productVariant->product->weight, $matches)) {
                    $weightInGrams = (int) $matches[1];
                }
            }

            $items[] = [
                'name' => "{$item->product_name} - {$item->variant_name}",
                'description' => "Insulated bottle SKU: {$item->sku}",
                'value' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'weight' => max(100, $weightInGrams),
            ];
        }

        $courierCompany = $courierOptions['courier_company'] ?? $this->courierCompany;
        $courierType = $courierOptions['courier_type'] ?? $this->courierType;
        $deliveryType = $courierOptions['delivery_type'] ?? 'later';
        $deliveryDate = $courierOptions['delivery_date'] ?? date('Y-m-d');
        $deliveryTime = $courierOptions['delivery_time'] ?? '09:00';

        $destinationAddress = trim(implode(', ', array_filter([
            $order->shippingAddress->address,
            $order->shippingAddress->city,
            $order->shippingAddress->province,
        ])));

        $payload = [
            'shipper_contact_name' => (string) ($this->origin['contact_name'] ?? 'Alexa Fulfillment Center'),
            'shipper_contact_phone' => (string) ($this->origin['contact_phone'] ?? '081299887766'),
            'shipper_contact_email' => (string) ($this->origin['contact_email'] ?? 'fulfillment@alexa.store'),
            'shipper_organization' => (string) ($this->origin['organization'] ?? 'Alexa Lifestyle Indonesia'),
            'origin_contact_name' => (string) ($this->origin['contact_name'] ?? 'Alexa Fulfillment Center'),
            'origin_contact_phone' => (string) ($this->origin['contact_phone'] ?? '081299887766'),
            'origin_address' => (string) ($this->origin['address'] ?? 'Jl. Jenderal Sudirman Kav. 52-53, SCBD'),
            'origin_postal_code' => (int) ($this->origin['postal_code'] ?? 12190),
            'origin_note' => (string) ($this->origin['note'] ?? 'Alexa Warehouse Gate 3'),
            'destination_contact_name' => (string) $order->shippingAddress->recipient_name,
            'destination_contact_phone' => (string) $order->shippingAddress->phone,
            'destination_contact_email' => (string) $order->user->email,
            'destination_address' => $destinationAddress,
            'destination_postal_code' => (int) $order->shippingAddress->postal_code,
            'destination_note' => 'Handle with care, premium insulated tumbler',
            'courier_company' => $courierCompany,
            'courier_type' => $courierType,
            'delivery_type' => $deliveryType,
            'delivery_date' => $deliveryDate,
            'delivery_time' => $deliveryTime,
            'order_note' => "Order {$order->order_number}",
            'items' => $items,
        ];

        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(15)->post("{$this->baseUrl}/v1/orders", $payload);

        if (!$response->successful()) {
            Log::error('Biteship Order Creation Failed', [
                'order_number' => $order->order_number,
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ]);

            $errorMessage = $response->json('error') ?? $response->json('message') ?? 'Failed to create shipment order with Biteship.';
            throw new Exception("Biteship API Error: {$errorMessage}");
        }

        $data = $response->json();

        return [
            'biteship_order_id' => $data['id'] ?? null,
            'biteship_tracking_id' => $data['courier']['tracking_id'] ?? null,
            'waybill_id' => $data['courier']['waybill_id'] ?? null,
            'courier_code' => $data['courier']['company'] ?? $courierCompany,
            'courier_service' => $data['courier']['type'] ?? $courierType,
            'courier_name' => $data['courier']['name'] ?? strtoupper($courierCompany),
            'tracking_url' => $data['courier']['link'] ?? null,
            'status' => $this->mapStatus($data['status'] ?? 'placed'),
            'payload' => $data,
        ];
    }

    /**
     * Retrieve order details from Biteship API Sandbox.
     *
     * @throws Exception
     */
    public function getOrder(string $biteshipOrderId, ?string $currentStatus = null): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Biteship API key is not configured.');
        }

        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Accept' => 'application/json',
        ])->timeout(15)->get("{$this->baseUrl}/v1/orders/{$biteshipOrderId}");

        if (!$response->successful()) {
            Log::warning('Biteship Order Query Failed', [
                'order_id' => $biteshipOrderId,
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ]);

            $errorMessage = $response->json('error') ?? $response->json('message') ?? "HTTP {$response->status()}";
            throw new Exception("Biteship Order Error: {$errorMessage}");
        }

        $data = $response->json();

        return [
            'biteship_order_id' => $data['id'] ?? $biteshipOrderId,
            'biteship_tracking_id' => $data['courier']['tracking_id'] ?? null,
            'waybill_id' => $data['courier']['waybill_id'] ?? null,
            'courier_code' => $data['courier']['company'] ?? null,
            'courier_service' => $data['courier']['type'] ?? null,
            'courier_name' => $data['courier']['name'] ?? null,
            'tracking_url' => $data['courier']['link'] ?? null,
            'history' => $data['courier']['history'] ?? [],
            'status' => $this->mapStatus($data['status'] ?? null, $currentStatus),
            'payload' => $data,
        ];
    }

    /**
     * Retrieve tracking details from Biteship API Sandbox using tracking ID or waybill.
     *
     * @throws Exception
     */
    public function getTracking(string $trackingIdOrWaybill, ?string $currentStatus = null): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Biteship API key is not configured.');
        }

        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Accept' => 'application/json',
        ])->timeout(15)->get("{$this->baseUrl}/v1/trackings/{$trackingIdOrWaybill}");

        if (!$response->successful()) {
            Log::warning('Biteship Tracking Query Failed', [
                'identifier' => $trackingIdOrWaybill,
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ]);

            $errorMessage = $response->json('error') ?? $response->json('message') ?? "HTTP {$response->status()}";
            throw new Exception("Biteship Tracking Error: {$errorMessage}");
        }

        $data = $response->json();

        return [
            'biteship_order_id' => $data['id'] ?? null,
            'biteship_tracking_id' => $data['tracking_id'] ?? null,
            'waybill_id' => $data['waybill_id'] ?? null,
            'courier_code' => $data['courier']['company'] ?? null,
            'courier_service' => $data['courier']['type'] ?? null,
            'courier_name' => $data['courier']['name'] ?? null,
            'tracking_url' => $data['link'] ?? null,
            'history' => $data['history'] ?? [],
            'status' => $this->mapStatus($data['status'] ?? null, $currentStatus),
            'payload' => $data,
        ];
    }

    /**
     * Map Biteship status strings to internal shipment status.
     */
    public function mapStatus(?string $biteshipStatus, ?string $currentStatus = null): string
    {
        if ($biteshipStatus === null || trim($biteshipStatus) === '') {
            return $currentStatus ?? 'pending';
        }

        $status = strtolower(trim($biteshipStatus));

        return match ($status) {
            'draft', 'placed', 'scheduled', 'allocated', 'picking_up' => 'pending',
            'picked' => 'shipped',
            'dropping_off', 'in_transit', 'on_process' => 'in_transit',
            'delivered' => 'delivered',
            'cancelled', 'rejected', 'courier_not_found', 'returned', 'disposed', 'lost' => 'cancelled',
            default => $currentStatus ?? 'pending',
        };
    }
}
