<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = [
        'business_name',
        'business_slug',
        'logo_path',
        'favicon_path',
        'owner_name',
        'owner_phone',
        'owner_email',
        'default_phone',
        'default_whatsapp_number',
        'default_email',
        'default_address',
        'default_google_maps_url',
        'timezone',
        'currency',
        'receipt_footer_text',
        'terms_and_conditions',
        'qris_expiry_minutes',
        'whatsapp_provider',
        'whatsapp_api_key',
        'whatsapp_sender_number',
        'midtrans_server_key',
        'midtrans_client_key',
        'midtrans_is_production',
    ];

    protected $hidden = [
        'whatsapp_api_key',
        'midtrans_server_key',
        'midtrans_client_key',
    ];

    protected $attributes = [
        'timezone' => 'Asia/Jakarta',
        'currency' => 'IDR',
        'qris_expiry_minutes' => 30,
        'midtrans_is_production' => false,
    ];

    protected function casts(): array
    {
        return [
            'qris_expiry_minutes' => 'integer',
            'whatsapp_api_key' => 'encrypted',
            'midtrans_server_key' => 'encrypted',
            'midtrans_client_key' => 'encrypted',
            'midtrans_is_production' => 'boolean',
        ];
    }
}
