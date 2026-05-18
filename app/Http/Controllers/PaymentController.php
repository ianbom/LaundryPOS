<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateQrisRequest;
use App\Http\Requests\PayCashRequest;
use App\Jobs\SendPaymentReceiptWhatsappJob;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Services\ActivityLogger;
use App\Services\MidtransPaymentService;
use App\Support\BusinessSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function payCash(PayCashRequest $request, Order $order, ActivityLogger $logger): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        $payment = DB::transaction(function () use ($order, $data, $request, $logger): Payment {
            $lockedOrder = Order::query()
                ->with('activePayment')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($lockedOrder->payment_status === 'paid', 422, 'Order already paid.');
            abort_if((float) $data['amount_paid'] < (float) $lockedOrder->grand_total, 422, 'Cash amount is less than grand total.');

            if ($lockedOrder->activePayment?->method === 'qris' && $lockedOrder->activePayment->status === 'pending') {
                $lockedOrder->activePayment->forceFill([
                    'status' => 'cancelled',
                    'is_active' => false,
                    'cancelled_at' => now(),
                ])->save();
            }

            Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $payment = Payment::query()->create([
                'outlet_id' => $lockedOrder->outlet_id,
                'order_id' => $lockedOrder->id,
                'provider' => 'manual',
                'method' => 'cash',
                'status' => 'paid',
                'is_active' => true,
                'amount' => $lockedOrder->grand_total,
                'amount_paid' => $data['amount_paid'],
                'change_amount' => (float) $data['amount_paid'] - (float) $lockedOrder->grand_total,
                'paid_at' => now(),
                'confirmed_by' => $request->user()->id,
                'raw_response' => [
                    'source' => 'manual_cash',
                    'notes' => $data['notes'] ?? null,
                ],
            ]);

            $oldStatus = $lockedOrder->order_status;
            $newStatus = $oldStatus === 'waiting_payment' ? 'received' : $oldStatus;

            $lockedOrder->forceFill([
                'active_payment_id' => $payment->id,
                'payment_status' => 'paid',
                'order_status' => $newStatus,
            ])->save();

            $this->recordStatusHistory($lockedOrder, $oldStatus, $newStatus, $request->user()->id, 'Payment received by cash.');
            $logger->log($request, 'cash_payment_confirmed', $payment, $lockedOrder->outlet_id, null, $payment->toArray());

            return $payment;
        });

        SendPaymentReceiptWhatsappJob::dispatch($order->id);

        if ($request->expectsJson()) {
            return response()->json($payment->fresh(['order', 'confirmedBy']));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cash payment confirmed.']);

        return back();
    }

    public function generateQris(
        GenerateQrisRequest $request,
        Order $order,
        MidtransPaymentService $midtrans,
        ActivityLogger $logger,
    ): JsonResponse|RedirectResponse {
        $settings = BusinessSettings::current();
        abort_if(blank(BusinessSettings::midtransServerKey($settings)), 422, 'Midtrans server key is not configured.');

        $payment = DB::transaction(function () use ($order, $request, $midtrans, $settings, $logger): Payment {
            $lockedOrder = Order::query()
                ->with('customer')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($lockedOrder->payment_status === 'paid', 422, 'Order already paid.');

            Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->where('is_active', true)
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'is_active' => false,
                    'cancelled_at' => now(),
                ]);

            $providerOrderId = $this->providerOrderId($lockedOrder);
            $response = $midtrans->createQrisCharge($lockedOrder, $settings, $providerOrderId);
            $qrisData = $midtrans->extractQrisData($response);
            $expiresAt = now()->addMinutes(max(1, (int) $settings->qris_expiry_minutes));

            $payment = Payment::query()->create([
                'outlet_id' => $lockedOrder->outlet_id,
                'order_id' => $lockedOrder->id,
                'provider' => 'midtrans',
                'method' => 'qris',
                'status' => 'pending',
                'is_active' => true,
                'amount' => $lockedOrder->grand_total,
                'provider_order_id' => $providerOrderId,
                'provider_transaction_id' => $response['transaction_id'] ?? null,
                'provider_reference_id' => $response['reference_id'] ?? $response['transaction_id'] ?? null,
                'qris_string' => $qrisData['qris_string'],
                'qris_url' => $qrisData['qris_url'],
                'payment_url' => $qrisData['payment_url'],
                'expired_at' => $expiresAt,
                'raw_response' => $response,
            ]);

            Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->where('id', '!=', $payment->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $lockedOrder->forceFill([
                'active_payment_id' => $payment->id,
                'payment_status' => 'pending',
                'order_status' => 'waiting_payment',
            ])->save();

            $logger->log($request, 'qris_payment_generated', $payment, $lockedOrder->outlet_id, null, $payment->toArray());

            return $payment;
        });

        if ($request->expectsJson()) {
            return response()->json($payment->fresh());
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'QRIS payment generated.']);

        return back();
    }

    private function recordStatusHistory(Order $order, string $oldStatus, string $newStatus, int $userId, string $notes): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $userId,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    private function providerOrderId(Order $order): string
    {
        return 'LDR-'.$order->id.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
    }
}
