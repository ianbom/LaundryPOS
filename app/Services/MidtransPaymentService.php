<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Order;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Arr;

class MidtransPaymentService
{
    public function __construct(private readonly HttpFactory $http) {}

    /**
     * @return array<string, mixed>
     */
    public function createQrisCharge(Order $order, BusinessSetting $settings, string $providerOrderId): array
    {
        $endpoint = $settings->midtrans_is_production
            ? 'https://api.midtrans.com/v2/charge'
            : 'https://api.sandbox.midtrans.com/v2/charge';

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $providerOrderId,
                'gross_amount' => (int) round((float) $order->grand_total),
            ],
            'customer_details' => [
                'first_name' => $order->customer?->name,
                'phone' => $order->customer?->whatsapp_number ?? $order->customer?->phone,
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        return $this->http
            ->withBasicAuth((string) $settings->midtrans_server_key, '')
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->connectTimeout(5)
            ->post($endpoint, $payload)
            ->throw()
            ->json();
    }

    public function signatureIsValid(array $payload, string $serverKey): bool
    {
        $signature = (string) ($payload['signature_key'] ?? '');

        if ($signature === '') {
            return false;
        }

        $expected = hash('sha512', implode('', [
            (string) ($payload['order_id'] ?? ''),
            (string) ($payload['status_code'] ?? ''),
            (string) ($payload['gross_amount'] ?? ''),
            $serverKey,
        ]));

        return hash_equals($expected, $signature);
    }

    /**
     * @return array{qris_url: ?string, qris_string: ?string, payment_url: ?string}
     */
    public function extractQrisData(array $response): array
    {
        $actions = collect(Arr::get($response, 'actions', []));

        return [
            'qris_url' => $actions->firstWhere('name', 'generate-qr-code')['url'] ?? null,
            'qris_string' => $response['qr_string'] ?? $response['qris_string'] ?? null,
            'payment_url' => $actions->firstWhere('name', 'deeplink-redirect')['url']
                ?? $response['redirect_url']
                ?? null,
        ];
    }
}
