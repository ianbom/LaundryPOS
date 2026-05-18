<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\WhatsappTemplate;
use Illuminate\Database\Seeder;

class WhatsappTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'payment_receipt' => [
                'title' => 'Bukti pembayaran',
                'body' => "Halo {customer_name}, pembayaran laundry kamu berhasil.\n\nInvoice: {invoice_number}\nMetode Pembayaran: {payment_method}\nTotal: {grand_total}\n\nTracking laundry:\n{tracking_url}\n\nTerima kasih sudah menggunakan layanan {business_name}.",
            ],
            'order_created' => [
                'title' => 'Order dibuat',
                'body' => "Halo {customer_name}, order laundry kamu sudah kami terima.\n\nInvoice: {invoice_number}\nTotal: {grand_total}\nEstimasi selesai: {estimated_done_at}\n\nTracking:\n{tracking_url}",
            ],
            'order_processing' => [
                'title' => 'Order diproses',
                'body' => "Halo {customer_name}, laundry kamu sedang diproses.\n\nInvoice: {invoice_number}\nStatus: {order_status}\nTracking: {tracking_url}",
            ],
            'order_ready' => [
                'title' => 'Siap diambil',
                'body' => "Halo {customer_name}, laundry kamu sudah selesai dan siap diambil.\n\nInvoice: {invoice_number}\nStatus: Siap Diambil\n\nTracking:\n{tracking_url}\n\nSilakan ambil laundry kamu di outlet kami. Terima kasih.",
            ],
            'order_completed' => [
                'title' => 'Order selesai',
                'body' => "Halo {customer_name}, laundry kamu sudah selesai.\n\nInvoice: {invoice_number}\nStatus: Selesai\n\nTerima kasih sudah menggunakan layanan {business_name}.",
            ],
            'payment_reminder' => [
                'title' => 'Pengingat pembayaran',
                'body' => "Halo {customer_name}, pembayaran untuk invoice {invoice_number} masih menunggu konfirmasi.\n\nTotal: {grand_total}\nLink pembayaran: {payment_url}",
            ],
            'custom' => [
                'title' => 'Pesan khusus',
                'body' => 'Halo {customer_name}, {custom_message}',
            ],
        ];

        Outlet::query()->get()->each(function (Outlet $outlet) use ($templates): void {
            foreach ($templates as $type => $template) {
                WhatsappTemplate::updateOrCreate(
                    [
                        'outlet_id' => $outlet->id,
                        'type' => $type,
                    ],
                    [
                        'title' => $template['title'],
                        'body' => $template['body'],
                        'is_active' => true,
                    ],
                );
            }
        });
    }
}
