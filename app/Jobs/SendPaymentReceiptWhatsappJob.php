<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\WhatsApp\FonnteWhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPaymentReceiptWhatsappJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $orderId) {}

    public function handle(FonnteWhatsAppService $whatsApp): void
    {
        $order = Order::query()->with(['activePayment', 'customer', 'outlet'])->find($this->orderId);

        if ($order !== null) {
            $whatsApp->sendPaymentReceipt($order);
        }
    }
}
