<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    public function run(): void
    {
        BusinessSetting::updateOrCreate(
            ['business_slug' => 'bersih-laundry'],
            [
                'business_name' => 'Bersih Laundry',
                'owner_name' => 'Raka Pradipta',
                'owner_phone' => '0812-8471-9203',
                'owner_email' => 'owner@bersihlaundry.test',
                'default_phone' => '031-847-1928',
                'default_whatsapp_number' => '6281284719203',
                'default_email' => 'hello@bersihlaundry.test',
                'default_address' => 'Jl. Raya Darmo No. 42, Surabaya',
                'default_google_maps_url' => 'https://maps.google.com/?q=Bersih+Laundry+Surabaya',
                'timezone' => 'Asia/Jakarta',
                'currency' => 'IDR',
                'receipt_footer_text' => 'Terima kasih sudah mempercayakan laundry Anda kepada Bersih Laundry.',
                'terms_and_conditions' => 'Komplain diterima maksimal 24 jam setelah pengambilan. Barang yang tidak diambil lebih dari 30 hari berada di luar tanggung jawab outlet.',
                'qris_expiry_minutes' => 30,
                'whatsapp_provider' => 'fonnte',
                'whatsapp_api_key' => 'demo-fonnte-api-key',
                'whatsapp_sender_number' => '6281284719203',
                'midtrans_server_key' => 'SB-Mid-server-demo-key',
                'midtrans_client_key' => 'SB-Mid-client-demo-key',
                'midtrans_is_production' => false,
            ],
        );
    }
}
