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
}
