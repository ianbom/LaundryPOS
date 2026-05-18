<?php

namespace App\Services\WhatsApp;

class WhatsAppTemplateRenderer
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function render(string $body, array $data): string
    {
        return preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function (array $matches) use ($data) {
            $key = $matches[1];

            return array_key_exists($key, $data)
                ? (string) $data[$key]
                : $matches[0];
        }, $body);
    }

    /**
     * @return array<string, string>
     */
    public function sampleData(): array
    {
        return [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '08123456789',
            'invoice_number' => 'LDR-20260518-0001',
            'grand_total' => 'Rp50.000',
            'payment_method' => 'QRIS',
            'payment_status' => 'paid',
            'order_status' => 'ready_to_pickup',
            'tracking_url' => 'https://example.com/track/sample-token',
            'invoice_url' => 'https://example.com/invoices/LDR-20260518-0001',
            'outlet_name' => 'Bersih Laundry Pusat',
            'outlet_phone' => '031123456',
            'outlet_whatsapp' => '08123456789',
            'outlet_address' => 'Jl. Contoh No. 1',
            'business_name' => 'Bersih Laundry',
            'estimated_done_at' => '18 Mei 2026 17:00',
            'paid_at' => '18 Mei 2026 10:00',
        ];
    }
}
