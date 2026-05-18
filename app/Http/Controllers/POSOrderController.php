<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePOSOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Outlet;
use App\Models\ServiceCategory;
use App\Services\ActivityLogger;
use App\Services\OrderPricingService;
use App\Support\BusinessSettings;
use App\Support\OutletAccess;
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

    public function store(StorePOSOrderRequest $request, OrderPricingService $pricing, ActivityLogger $logger): RedirectResponse
    {
        $order = DB::transaction(function () use ($request, $pricing) {
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

            $order = Order::query()->create([
                'outlet_id' => $request->integer('outlet_id'),
                'customer_id' => $customer->id,
                'created_by' => $request->user()->id,
                'invoice_number' => $this->nextInvoiceNumber($request->integer('outlet_id')),
                'order_status' => 'waiting_payment',
                'payment_status' => 'unpaid',
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

            $customer->increment('total_orders');

            return $order;
        });

        $logger->log($request, 'order_created', $order, $order->outlet_id, null, $order->fresh()->toArray());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Order created.']);

        return redirect()->route('orders.show', $order);
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
