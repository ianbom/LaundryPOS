<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePOSOrderRequest;
use App\Jobs\SendPaymentReceiptWhatsappJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\PosPaymentIntent;
use App\Models\ServiceCategory;
use App\Services\ActivityLogger;
use App\Services\MidtransPaymentService;
use App\Services\OrderPricingService;
use App\Support\BusinessSettings;
use App\Support\OutletAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class POSOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $outletId = OutletAccess::activeOutletId($request->user());
        abort_unless($outletId && OutletAccess::canManageOrders($request->user(), $outletId), 403);

        $categories = ServiceCategory::query()
            ->with(['services' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->with(['variants' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name')]);
            }])
            ->where('outlet_id', $outletId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('pos/orders/create', [
            'outlet' => Outlet::query()->findOrFail($outletId),
            'customers' => Customer::query()
                ->where('outlet_id', $outletId)
                ->latest()
                ->limit(30)
                ->get(['id', 'name', 'phone', 'whatsapp_number', 'address', 'notes']),
            'serviceCategories' => $categories,
            'businessSettings' => BusinessSettings::current(),
        ]);
    }

    public function store(
        StorePOSOrderRequest $request,
        OrderPricingService $pricing,
        ActivityLogger $logger,
    ): RedirectResponse {
        abort_unless($request->string('payment_method')->toString() === 'cash', 422, 'QRIS order must be paid before order is created.');

        $order = DB::transaction(function () use ($request, $pricing, $logger) {
            $customer = $request->customer();
            $variants = $request->serviceVariants();
            $snapshots = [];

            foreach ($request->input('items') as $item) {
                $snapshot = $pricing->calculateItem($variants[(int) $item['service_variant_id']], $item['quantity']);
                $snapshot['notes'] = $item['notes'] ?? null;
                $snapshots[] = $snapshot;
            }

            $totals = $pricing->calculateOrderTotals(
                $snapshots,
                $request->input('discount_amount', 0),
                $request->input('additional_fee', 0),
                $request->input('delivery_fee', 0),
            );
            abort_if(
                (float) $request->input('amount_paid') < $totals['grand_total'],
                422,
                'Cash amount is less than grand total.',
            );

            $order = Order::query()->create([
                'outlet_id' => $request->integer('outlet_id'),
                'customer_id' => $customer->id,
                'created_by' => $request->user()->id,
                'invoice_number' => $this->nextInvoiceNumber($request->integer('outlet_id')),
                'order_status' => 'waiting_payment',
                'payment_status' => 'paid',
                'order_date' => now(),
                'estimated_done_at' => $request->date('estimated_done_at'),
                'customer_notes' => $request->input('customer_notes'),
                'internal_notes' => $request->input('internal_notes'),
                'tracking_token' => $this->trackingToken(),
                ...$totals,
            ]);

            $order->items()->createMany($snapshots);
            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'old_status' => null,
                'new_status' => 'waiting_payment',
                'changed_by' => $request->user()->id,
                'notes' => 'Order created from POS.',
                'created_at' => now(),
            ]);

            $payment = $this->createCashPayment($request, $order);

            $order->forceFill([
                'active_payment_id' => $payment->id,
                'payment_status' => $payment->status,
                'order_status' => 'received',
            ])->save();

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'old_status' => 'waiting_payment',
                'new_status' => 'received',
                'changed_by' => $request->user()->id,
                'notes' => 'Payment received by cash from POS.',
                'created_at' => now(),
            ]);

            $customer->increment('total_orders');
            $logger->log($request, 'order_created', $order, $order->outlet_id, null, $order->fresh()->toArray());
            $logger->log(
                $request,
                'cash_payment_confirmed',
                $payment,
                $order->outlet_id,
                null,
                $payment->toArray(),
            );

            return $order->fresh();
        });

        if ($order->payment_status === 'paid') {
            SendPaymentReceiptWhatsappJob::dispatch($order->id);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Order created and cash payment confirmed.',
        ]);

        return redirect()->route('orders.show', $order);
    }

    public function generateQris(StorePOSOrderRequest $request, OrderPricingService $pricing, MidtransPaymentService $midtrans): JsonResponse
    {
        abort_unless($request->string('payment_method')->toString() === 'qris', 422, 'Payment method must be QRIS.');

        $prepared = $this->prepareOrderPayload($request, $pricing);
        $settings = BusinessSettings::current();
        abort_if(blank(BusinessSettings::midtransServerKey($settings)), 422, 'Midtrans server key is not configured.');

        $providerOrderId = 'POS-'.$request->integer('outlet_id').'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8));
        $customer = $request->array('customer');
        $response = $midtrans->createQrisChargePayload(
            amount: (float) $prepared['totals']['grand_total'],
            customerName: $customer['name'] ?? null,
            customerPhone: ($customer['whatsapp_number'] ?? null) ?: ($customer['phone'] ?? null),
            settings: $settings,
            providerOrderId: $providerOrderId,
        );
        $qrisData = $midtrans->extractQrisData($response);

        $intent = PosPaymentIntent::query()->create([
            'outlet_id' => $request->integer('outlet_id'),
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'amount' => $prepared['totals']['grand_total'],
            'order_payload' => $request->safe()->except(['amount_paid']),
            'order_snapshots' => $prepared['snapshots'],
            'order_totals' => $prepared['totals'],
            'provider_order_id' => $providerOrderId,
            'provider_transaction_id' => $response['transaction_id'] ?? null,
            'provider_reference_id' => $response['reference_id'] ?? $response['transaction_id'] ?? null,
            'qris_string' => $qrisData['qris_string'],
            'qris_url' => $qrisData['qris_url'],
            'payment_url' => $qrisData['payment_url'],
            'expired_at' => now()->addMinutes(max(1, (int) $settings->qris_expiry_minutes)),
            'raw_response' => $response,
        ]);

        return response()->json([
            'intent_id' => $intent->id,
            'status' => $intent->status,
            'amount' => $intent->amount,
            'provider_order_id' => $intent->provider_order_id,
            'qris_url' => $intent->qris_url,
            'qris_string' => $intent->qris_string,
            'payment_url' => $intent->payment_url,
            'expired_at' => $intent->expired_at?->toIso8601String(),
        ]);
    }

    public function qrisStatus(PosPaymentIntent $intent): JsonResponse
    {
        abort_unless(OutletAccess::canManagePayments(request()->user(), (int) $intent->outlet_id), 403);

        return response()->json([
            'status' => $intent->status,
            'order_id' => $intent->order_id,
            'order_url' => $intent->order_id ? route('orders.show', $intent->order_id) : null,
        ]);
    }

    /**
     * @return array{snapshots: array<int, array<string, mixed>>, totals: array<string, float>}
     */
    private function prepareOrderPayload(StorePOSOrderRequest $request, OrderPricingService $pricing): array
    {
        $variants = $request->serviceVariants();
        $snapshots = [];

        foreach ($request->input('items') as $item) {
            $snapshot = $pricing->calculateItem($variants[(int) $item['service_variant_id']], $item['quantity']);
            $snapshot['notes'] = $item['notes'] ?? null;
            $snapshots[] = $snapshot;
        }

        return [
            'snapshots' => $snapshots,
            'totals' => $pricing->calculateOrderTotals(
                $snapshots,
                $request->input('discount_amount', 0),
                $request->input('additional_fee', 0),
                $request->input('delivery_fee', 0),
            ),
        ];
    }

    private function createCashPayment(StorePOSOrderRequest $request, Order $order): Payment
    {
        $amountPaid = (float) $request->input('amount_paid');

        return Payment::query()->create([
            'outlet_id' => $order->outlet_id,
            'order_id' => $order->id,
            'provider' => 'manual',
            'method' => 'cash',
            'status' => 'paid',
            'is_active' => true,
            'amount' => $order->grand_total,
            'amount_paid' => $amountPaid,
            'change_amount' => $amountPaid - (float) $order->grand_total,
            'paid_at' => now(),
            'confirmed_by' => $request->user()->id,
            'raw_response' => [
                'source' => 'pos_order_create',
            ],
        ]);
    }

    private function nextInvoiceNumber(int $outletId): string
    {
        $outlet = Outlet::query()->findOrFail($outletId);
        $date = now()->format('Ymd');
        $prefix = 'LDR-'.($outlet->code ?: Str::upper(Str::substr($outlet->slug, 0, 3)))."-{$date}-";
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
