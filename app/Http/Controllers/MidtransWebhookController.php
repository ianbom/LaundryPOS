<?php

namespace App\Http\Controllers;

use App\Jobs\SendPaymentReceiptWhatsappJob;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentWebhook;
use App\Models\PosPaymentIntent;
use App\Services\MidtransPaymentService;
use App\Support\BusinessSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request, MidtransPaymentService $midtrans): JsonResponse
    {
        $payload = $request->all();
        $settings = BusinessSettings::current();
        $serverKey = BusinessSettings::midtransServerKey($settings);
        $providerOrderId = (string) ($payload['order_id'] ?? '');
        $transactionId = (string) ($payload['transaction_id'] ?? '');
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $grossAmount = $payload['gross_amount'] ?? null;

        $payment = $this->findPayment($providerOrderId, $transactionId);
        $intent = $payment === null ? $this->findIntent($providerOrderId, $transactionId) : null;
        $isValidSignature = filled($serverKey)
            && $midtrans->signatureIsValid($payload, (string) $serverKey);

        $duplicateExists = PaymentWebhook::query()
            ->where('provider', 'midtrans')
            ->where('provider_order_id', $providerOrderId)
            ->where('provider_transaction_id', $transactionId)
            ->where('transaction_status', $transactionStatus)
            ->where('is_processed', true)
            ->exists();

        $webhook = PaymentWebhook::query()->create([
            'payment_id' => $payment?->id ?? $intent?->payment_id,
            'order_id' => $payment?->order_id ?? $intent?->order_id,
            'provider' => 'midtrans',
            'provider_order_id' => $providerOrderId ?: null,
            'provider_transaction_id' => $transactionId ?: null,
            'event_type' => $payload['status_message'] ?? 'payment.notification',
            'transaction_status' => $transactionStatus ?: null,
            'fraud_status' => $payload['fraud_status'] ?? null,
            'payment_type' => $payload['payment_type'] ?? null,
            'gross_amount' => $grossAmount,
            'signature_key' => $payload['signature_key'] ?? null,
            'is_valid_signature' => $isValidSignature,
            'raw_payload' => $payload,
            'created_at' => now(),
        ]);

        if (! $isValidSignature) {
            return $this->finish($webhook, 'failed', 'Invalid Midtrans signature.');
        }

        if ($payment === null && $intent === null) {
            return $this->finish($webhook, 'ignored', 'Payment was not found.');
        }

        if ($payment === null && $intent !== null) {
            $sendReceipt = $this->processPosIntentWebhook($webhook, $intent, $grossAmount, $payload);

            if ($sendReceipt) {
                SendPaymentReceiptWhatsappJob::dispatch((int) $webhook->fresh()->order_id);
            }

            return response()->json([
                'status' => $webhook->fresh()->process_status,
                'message' => $webhook->fresh()->process_message,
            ]);
        }

        if ($duplicateExists) {
            return $this->finish($webhook, 'duplicate', 'Duplicate webhook ignored.');
        }

        $sendReceipt = false;

        DB::transaction(function () use ($webhook, $payment, $grossAmount, $payload, &$sendReceipt): void {
            $lockedPayment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::query()->whereKey($lockedPayment->order_id)->lockForUpdate()->firstOrFail();

            $webhook->forceFill([
                'payment_id' => $lockedPayment->id,
                'order_id' => $order->id,
            ])->save();

            if ($order->payment_status === 'paid' && (int) $order->active_payment_id !== (int) $lockedPayment->id) {
                $this->markWebhook($webhook, 'conflict', 'Order already paid by another payment.');

                return;
            }

            if (! $this->amountMatches($grossAmount, $lockedPayment->amount)) {
                $this->markWebhook($webhook, 'failed', 'Gross amount does not match payment amount.');

                return;
            }

            if (! $lockedPayment->is_active || $lockedPayment->status !== 'pending' || (int) $order->active_payment_id !== (int) $lockedPayment->id) {
                $this->markWebhook($webhook, 'conflict', 'Payment is not the active pending payment.');

                return;
            }

            $mappedStatus = $this->mapTransactionStatus(
                (string) ($payload['transaction_status'] ?? ''),
                $payload['fraud_status'] ?? null,
            );

            if ($mappedStatus === 'paid') {
                $oldStatus = $order->order_status;
                $newStatus = $oldStatus === 'waiting_payment' ? 'received' : $oldStatus;

                $lockedPayment->forceFill([
                    'status' => 'paid',
                    'paid_at' => now(),
                ])->save();

                $order->forceFill([
                    'payment_status' => 'paid',
                    'order_status' => $newStatus,
                    'active_payment_id' => $lockedPayment->id,
                ])->save();

                $this->recordStatusHistory($order, $oldStatus, $newStatus, 'Payment confirmed by Midtrans webhook.');
                $this->markWebhook($webhook, 'processed', 'QRIS payment marked as paid.');
                $this->logActivity($order, $lockedPayment, 'qris_payment_paid');
                $sendReceipt = true;

                return;
            }

            if (in_array($mappedStatus, ['expired', 'cancelled', 'failed'], true)) {
                $lockedPayment->forceFill([
                    'status' => $mappedStatus,
                    'is_active' => false,
                    'cancelled_at' => $mappedStatus === 'cancelled' ? now() : $lockedPayment->cancelled_at,
                ])->save();

                $order->forceFill([
                    'payment_status' => $mappedStatus,
                    'active_payment_id' => (int) $order->active_payment_id === (int) $lockedPayment->id ? null : $order->active_payment_id,
                ])->save();

                $this->markWebhook($webhook, $mappedStatus, 'QRIS payment marked as '.$mappedStatus.'.');
                $this->logActivity($order, $lockedPayment, 'qris_payment_'.$mappedStatus);

                return;
            }

            $this->markWebhook($webhook, 'ignored', 'Transaction status is not actionable.');
        });

        if ($sendReceipt) {
            SendPaymentReceiptWhatsappJob::dispatch((int) $payment->order_id);
        }

        return response()->json([
            'status' => $webhook->fresh()->process_status,
            'message' => $webhook->fresh()->process_message,
        ]);
    }

    private function findPayment(string $providerOrderId, string $transactionId): ?Payment
    {
        return Payment::query()
            ->where('provider', 'midtrans')
            ->where(function ($query) use ($providerOrderId, $transactionId) {
                $query->when($providerOrderId !== '', fn ($query) => $query->orWhere('provider_order_id', $providerOrderId))
                    ->when($transactionId !== '', fn ($query) => $query->orWhere('provider_transaction_id', $transactionId))
                    ->when($transactionId !== '', fn ($query) => $query->orWhere('provider_reference_id', $transactionId));
            })
            ->first();
    }

    private function findIntent(string $providerOrderId, string $transactionId): ?PosPaymentIntent
    {
        return PosPaymentIntent::query()
            ->where(function ($query) use ($providerOrderId, $transactionId) {
                $query->when($providerOrderId !== '', fn ($query) => $query->orWhere('provider_order_id', $providerOrderId))
                    ->when($transactionId !== '', fn ($query) => $query->orWhere('provider_transaction_id', $transactionId))
                    ->when($transactionId !== '', fn ($query) => $query->orWhere('provider_reference_id', $transactionId));
            })
            ->first();
    }

    private function processPosIntentWebhook(PaymentWebhook $webhook, PosPaymentIntent $intent, mixed $grossAmount, array $payload): bool
    {
        $sendReceipt = false;

        DB::transaction(function () use ($webhook, $intent, $grossAmount, $payload, &$sendReceipt): void {
            $lockedIntent = PosPaymentIntent::query()->whereKey($intent->id)->lockForUpdate()->firstOrFail();

            if (! $this->amountMatches($grossAmount, $lockedIntent->amount)) {
                $this->markWebhook($webhook, 'failed', 'Gross amount does not match POS payment intent amount.');

                return;
            }

            if ($lockedIntent->status === 'paid' && $lockedIntent->order_id !== null) {
                $this->markWebhook($webhook, 'duplicate', 'POS payment intent already finalized.');

                return;
            }

            $mappedStatus = $this->mapTransactionStatus(
                (string) ($payload['transaction_status'] ?? ''),
                $payload['fraud_status'] ?? null,
            );

            if ($mappedStatus !== 'paid') {
                if (in_array($mappedStatus, ['expired', 'cancelled', 'failed'], true)) {
                    $lockedIntent->forceFill(['status' => $mappedStatus])->save();
                    $this->markWebhook($webhook, $mappedStatus, 'POS payment intent marked as '.$mappedStatus.'.');

                    return;
                }

                $this->markWebhook($webhook, 'ignored', 'Transaction status is not actionable.');

                return;
            }

            [$order, $payment] = $this->createOrderFromIntent($lockedIntent);

            $lockedIntent->forceFill([
                'status' => 'paid',
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'paid_at' => now(),
            ])->save();

            $webhook->forceFill([
                'payment_id' => $payment->id,
                'order_id' => $order->id,
            ])->save();

            $this->markWebhook($webhook, 'processed', 'POS QRIS paid and order created.');
            $this->logActivity($order, $payment, 'pos_qris_order_created');
            $sendReceipt = true;
        });

        return $sendReceipt;
    }

    /**
     * @return array{0: Order, 1: Payment}
     */
    private function createOrderFromIntent(PosPaymentIntent $intent): array
    {
        $payload = $intent->order_payload;
        $customer = $this->resolveIntentCustomer($intent);

        $order = Order::query()->create([
            'outlet_id' => $intent->outlet_id,
            'customer_id' => $customer->id,
            'created_by' => $intent->user_id,
            'invoice_number' => $this->nextInvoiceNumber((int) $intent->outlet_id),
            'order_status' => 'received',
            'payment_status' => 'paid',
            'order_date' => now(),
            'estimated_done_at' => $payload['estimated_done_at'] ?? null,
            'customer_notes' => $payload['customer_notes'] ?? null,
            'internal_notes' => $payload['internal_notes'] ?? null,
            'tracking_token' => $this->trackingToken(),
            ...$intent->order_totals,
        ]);

        $order->items()->createMany($intent->order_snapshots);

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'old_status' => null,
            'new_status' => 'waiting_payment',
            'changed_by' => $intent->user_id,
            'notes' => 'Order created after POS QRIS payment.',
            'created_at' => now(),
        ]);

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'old_status' => 'waiting_payment',
            'new_status' => 'received',
            'changed_by' => $intent->user_id,
            'notes' => 'Payment confirmed by Midtrans before order creation.',
            'created_at' => now(),
        ]);

        $payment = Payment::query()->create([
            'outlet_id' => $intent->outlet_id,
            'order_id' => $order->id,
            'provider' => 'midtrans',
            'method' => 'qris',
            'status' => 'paid',
            'is_active' => true,
            'amount' => $intent->amount,
            'provider_order_id' => $intent->provider_order_id,
            'provider_transaction_id' => $intent->provider_transaction_id,
            'provider_reference_id' => $intent->provider_reference_id,
            'qris_string' => $intent->qris_string,
            'qris_url' => $intent->qris_url,
            'payment_url' => $intent->payment_url,
            'expired_at' => $intent->expired_at,
            'paid_at' => now(),
            'raw_response' => $intent->raw_response,
        ]);

        $order->forceFill(['active_payment_id' => $payment->id])->save();
        $customer->increment('total_orders');
        $customer->increment('total_spent', (float) $order->grand_total);

        return [$order->fresh(), $payment->fresh()];
    }

    private function resolveIntentCustomer(PosPaymentIntent $intent): Customer
    {
        $payload = $intent->order_payload;

        if (! empty($payload['customer_id'])) {
            return Customer::query()->findOrFail($payload['customer_id']);
        }

        $customer = $payload['customer'];

        return Customer::query()->create([
            'outlet_id' => $intent->outlet_id,
            'name' => $customer['name'],
            'phone' => $customer['phone'],
            'whatsapp_number' => ($customer['whatsapp_number'] ?? null) ?: $customer['phone'],
            'address' => $customer['address'] ?? null,
            'notes' => $customer['notes'] ?? null,
        ]);
    }

    private function finish(PaymentWebhook $webhook, string $status, string $message): JsonResponse
    {
        $this->markWebhook($webhook, $status, $message);

        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }

    private function markWebhook(PaymentWebhook $webhook, string $status, string $message): void
    {
        $webhook->forceFill([
            'is_processed' => true,
            'processed_at' => now(),
            'process_status' => $status,
            'process_message' => $message,
        ])->save();
    }

    private function mapTransactionStatus(string $status, ?string $fraudStatus): string
    {
        if ($status === 'settlement' || ($status === 'capture' && in_array($fraudStatus, [null, '', 'accept'], true))) {
            return 'paid';
        }

        return match ($status) {
            'expire' => 'expired',
            'cancel' => 'cancelled',
            'deny', 'failure' => 'failed',
            default => 'ignored',
        };
    }

    private function amountMatches(mixed $grossAmount, mixed $paymentAmount): bool
    {
        return abs((float) $grossAmount - (float) $paymentAmount) < 0.01;
    }

    private function recordStatusHistory(Order $order, string $oldStatus, string $newStatus, string $notes): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => null,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    private function logActivity(Order $order, Payment $payment, string $action): void
    {
        ActivityLog::query()->create([
            'outlet_id' => $order->outlet_id,
            'subject_type' => $payment->getMorphClass(),
            'subject_id' => $payment->id,
            'action' => $action,
            'new_values' => $payment->fresh()->toArray(),
            'created_at' => now(),
        ]);
    }

    private function nextInvoiceNumber(int $outletId): string
    {
        $outletCode = (string) DB::table('outlets')->where('id', $outletId)->value('code');
        $outletSlug = (string) DB::table('outlets')->where('id', $outletId)->value('slug');
        $date = now()->format('Ymd');
        $prefix = 'LDR-'.($outletCode ?: Str::upper(Str::substr($outletSlug, 0, 3)))."-{$date}-";
        $lastInvoice = Order::query()
            ->where('outlet_id', $outletId)
            ->where('invoice_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');
        $sequence = $lastInvoice ? ((int) Str::afterLast($lastInvoice, '-') + 1) : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function trackingToken(): string
    {
        do {
            $token = 'trk_'.Str::random(32);
        } while (Order::query()->where('tracking_token', $token)->exists());

        return $token;
    }
}
