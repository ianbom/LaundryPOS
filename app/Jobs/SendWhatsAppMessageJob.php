<?php

namespace App\Jobs;

use App\Models\WhatsappMessage;
use App\Services\WhatsApp\FonnteWhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $whatsappMessageId) {}

    public function handle(FonnteWhatsAppService $service): void
    {
        $message = WhatsappMessage::query()->find($this->whatsappMessageId);

        if ($message !== null) {
            $service->deliver($message);
        }
    }
}
