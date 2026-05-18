<?php

namespace App\Services\WhatsApp;

use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use App\Support\BusinessSettings;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\URL;
use Throwable;

class FonnteWhatsAppService
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly WhatsAppTemplateRenderer $renderer,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function sendMessage(string $phone, string $message, array $metadata = []): WhatsappMessage
    {
        $whatsappMessage = WhatsappMessage::query()->create([
            'outlet_id' => $metadata['outlet_id'] ?? Outlet::query()->value('id'),
            'order_id' => $metadata['order_id'] ?? null,
            'customer_id' => $metadata['customer_id'] ?? null,
            'provider' => 'fonnte',
            'phone' => $this->normalizeIndonesianPhoneNumber($phone),
            'message_type' => $metadata['message_type'] ?? 'custom',
            'message_body' => $message,
            'status' => 'pending',
        ]);

        try {
            SendWhatsAppMessageJob::dispatch($whatsappMessage->id);
        } catch (Throwable $exception) {
            $whatsappMessage->forceFill([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ])->save();
        }

        return $whatsappMessage->fresh();
    }

    public function deliver(WhatsappMessage $message): WhatsappMessage
    {
        if ($message->status !== 'pending') {
            return $message;
        }

        $settings = BusinessSettings::current();

        if ($settings->whatsapp_provider !== 'fonnte' || blank($settings->whatsapp_api_key)) {
            return $this->markFailed($message, 'Fonnte WhatsApp configuration is incomplete.');
        }

        try {
            $response = $this->http
                ->asForm()
                ->withHeaders(['Authorization' => $settings->whatsapp_api_key])
                ->timeout(10)
                ->post('https://api.fonnte.com/send', [
                    'target' => $message->phone,
                    'message' => $message->message_body,
                    'countryCode' => '62',
                ]);

            $payload = $response->json();
            $success = $response->successful() && (bool) data_get($payload, 'status', false);

            if (! $success) {
                return $this->markFailed(
                    $message,
                    (string) (data_get($payload, 'reason') ?: data_get($payload, 'message') ?: $response->body()),
                    is_array($payload) ? $payload : ['body' => $response->body()],
                );
            }

            $message->forceFill([
                'status' => 'sent',
                'provider_message_id' => $this->providerMessageId($payload),
                'raw_response' => $payload,
                'sent_at' => now(),
            ])->save();

            return $message->fresh();
        } catch (Throwable $exception) {
            return $this->markFailed($message, $exception->getMessage());
        }
    }

    public function sendPaymentReceipt(Order $order): ?WhatsappMessage
    {
        if ($order->payment_status !== 'paid') {
            return null;
        }

        return $this->sendOrderTemplate($order, 'payment_receipt');
    }

    public function sendOrderReady(Order $order, bool $manual = false): ?WhatsappMessage
    {
        if (! $manual && $this->hasSentMessage($order, 'order_ready')) {
            return null;
        }

        return $this->sendOrderTemplate($order, 'order_ready');
    }

    public function sendOrderCompleted(Order $order, bool $manual = false): ?WhatsappMessage
    {
        if (! $manual && $this->hasSentMessage($order, 'order_completed')) {
            return null;
        }

        return $this->sendOrderTemplate($order, 'order_completed');
    }

    public function sendCustomOrderMessage(Order $order, string $message): WhatsappMessage
    {
        $order->loadMissing(['customer', 'outlet']);

        return $this->sendMessage($this->orderPhone($order), $message, [
            'outlet_id' => $order->outlet_id,
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'message_type' => 'custom',
        ]);
    }

    public function normalizeIndonesianPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone) ?? '';
        $phone = ltrim($phone, '+');

        if (str_starts_with($phone, '0')) {
            return '62'.substr($phone, 1);
        }

        return $phone;
    }

    private function sendOrderTemplate(Order $order, string $type): WhatsappMessage
    {
        $order->loadMissing(['customer', 'outlet', 'activePayment']);
        $template = $this->templateFor($order, $type);
        $body = $this->renderer->render($template, $this->templateData($order));

        return $this->sendMessage($this->orderPhone($order), $body, [
            'outlet_id' => $order->outlet_id,
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'message_type' => $type,
        ]);
    }

    private function hasSentMessage(Order $order, string $type): bool
    {
        return $order->whatsappMessages()
            ->where('message_type', $type)
            ->where('status', 'sent')
            ->exists();
    }

    private function templateFor(Order $order, string $type): string
    {
        $template = WhatsappTemplate::query()
            ->where('type', $type)
            ->where('is_active', true)
            ->where(fn ($query) => $query->where('outlet_id', $order->outlet_id)->orWhereNull('outlet_id'))
            ->orderByRaw('case when outlet_id is null then 1 else 0 end')
            ->first();

        return $template?->body ?? $this->defaultTemplate($type);
    }

    /**
     * @return array<string, string>
     */
    private function templateData(Order $order): array
    {
        $settings = BusinessSettings::current();
        $payment = $order->activePayment;

        return [
            'customer_name' => $order->customer->name,
            'customer_phone' => $order->customer->phone,
            'invoice_number' => $order->invoice_number,
            'grand_total' => $this->formatRupiah($order->grand_total),
            'payment_method' => $payment ? strtoupper($payment->method) : '-',
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,
            'tracking_url' => URL::to("/track/{$order->tracking_token}"),
            'invoice_url' => URL::to("/public/invoice/{$order->tracking_token}"),
            'outlet_name' => $order->outlet->name,
            'outlet_phone' => $order->outlet->phone ?? '-',
            'outlet_whatsapp' => $order->outlet->whatsapp_number ?? $order->outlet->phone ?? '-',
            'outlet_address' => $order->outlet->address ?? '-',
            'business_name' => $settings->business_name,
            'estimated_done_at' => $order->estimated_done_at?->translatedFormat('d M Y H:i') ?? '-',
            'paid_at' => $payment?->paid_at?->translatedFormat('d M Y H:i') ?? '-',
        ];
    }

    private function defaultTemplate(string $type): string
    {
        return match ($type) {
            'payment_receipt' => "Halo {customer_name}, pembayaran laundry kamu berhasil.\n\nInvoice: {invoice_number}\nTotal: {grand_total}\nMetode Pembayaran: {payment_method}\nStatus: Lunas\n\nTracking laundry:\n{tracking_url}\n\nTerima kasih sudah menggunakan layanan {business_name}.",
            'order_ready' => "Halo {customer_name}, laundry kamu sudah selesai dan siap diambil.\n\nInvoice: {invoice_number}\nStatus: Siap Diambil\n\nTracking laundry:\n{tracking_url}\n\nSilakan ambil di outlet {outlet_name}.\nTerima kasih.",
            'order_completed' => "Halo {customer_name}, laundry kamu sudah selesai.\n\nInvoice: {invoice_number}\nStatus: Selesai\n\nTerima kasih sudah menggunakan layanan {business_name}.",
            default => '{custom_message}',
        };
    }

    private function orderPhone(Order $order): string
    {
        return $order->customer->whatsapp_number ?: $order->customer->phone;
    }

    private function formatRupiah(mixed $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function providerMessageId(?array $payload): ?string
    {
        $id = data_get($payload, 'id.0') ?: data_get($payload, 'id') ?: data_get($payload, 'requestid');

        return is_scalar($id) ? (string) $id : null;
    }

    /**
     * @param  array<string, mixed>|null  $rawResponse
     */
    private function markFailed(WhatsappMessage $message, string $error, ?array $rawResponse = null): WhatsappMessage
    {
        $message->forceFill([
            'status' => 'failed',
            'error_message' => $error,
            'raw_response' => $rawResponse,
        ])->save();

        return $message->fresh();
    }
}
