<?php

namespace App\Support;

use App\Models\BusinessSetting;

class BusinessSettings
{
    public static function current(): BusinessSetting
    {
        return BusinessSetting::query()->firstOrCreate(
            [],
            [
                'business_name' => config('app.name', 'Laundry POS'),
                'timezone' => 'Asia/Jakarta',
                'currency' => 'IDR',
                'qris_expiry_minutes' => 30,
            ],
        );
    }

    public static function midtransServerKey(?BusinessSetting $settings = null): ?string
    {
        return filled(config('services.midtrans.server_key'))
            ? (string) config('services.midtrans.server_key')
            : $settings?->midtrans_server_key;
    }

    public static function midtransClientKey(?BusinessSetting $settings = null): ?string
    {
        return filled(config('services.midtrans.client_key'))
            ? (string) config('services.midtrans.client_key')
            : $settings?->midtrans_client_key;
    }

    public static function midtransIsProduction(?BusinessSetting $settings = null): bool
    {
        if (config('services.midtrans.is_production') !== null) {
            return filter_var(config('services.midtrans.is_production'), FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) $settings?->midtrans_is_production;
    }

    public static function fonnteApiKey(?BusinessSetting $settings = null): ?string
    {
        return filled(config('services.fonnte.api_key'))
            ? (string) config('services.fonnte.api_key')
            : $settings?->whatsapp_api_key;
    }
}
