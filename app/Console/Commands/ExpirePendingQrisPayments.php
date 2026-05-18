<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpirePendingQrisPayments extends Command
{
    protected $signature = 'payments:expire-qris';

    protected $description = 'Expire active pending QRIS payments after their expiry time.';

    public function handle(): int
    {
        $expired = 0;

        Payment::query()
            ->where('provider', 'midtrans')
            ->where('method', 'qris')
            ->where('status', 'pending')
            ->where('is_active', true)
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now())
            ->pluck('id')
            ->each(function (int $paymentId) use (&$expired): void {
                DB::transaction(function () use ($paymentId, &$expired): void {
                    $payment = Payment::query()->whereKey($paymentId)->lockForUpdate()->first();

                    if ($payment === null || $payment->status !== 'pending' || ! $payment->is_active) {
                        return;
                    }

                    $order = Order::query()->whereKey($payment->order_id)->lockForUpdate()->first();

                    $payment->forceFill([
                        'status' => 'expired',
                        'is_active' => false,
                    ])->save();

                    if ($order !== null && (int) $order->active_payment_id === (int) $payment->id && $order->payment_status !== 'paid') {
                        $order->forceFill([
                            'payment_status' => 'expired',
                            'active_payment_id' => null,
                        ])->save();
                    }

                    ActivityLog::query()->create([
                        'outlet_id' => $payment->outlet_id,
                        'subject_type' => $payment->getMorphClass(),
                        'subject_id' => $payment->id,
                        'action' => 'qris_payment_expired',
                        'new_values' => $payment->fresh()->toArray(),
                        'created_at' => now(),
                    ]);

                    $expired++;
                });
            });

        $this->info("Expired {$expired} pending QRIS payment(s).");

        return self::SUCCESS;
    }
}
