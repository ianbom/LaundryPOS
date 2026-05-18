<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\OrderStatusService;
use App\Support\OutletAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function transactions(Request $request, OrderStatusService $statusService)
    {
        $outletIds = $this->reportOutletIds($request, $statusService);
        $orders = $this->transactionQuery($request, $outletIds)
            ->with(['outlet:id,name', 'customer:id,name,phone', 'activePayment:id,order_id,method,paid_at', 'creator:id,name'])
            ->latest('order_date')
            ->get();

        if ($request->string('export')->toString() === 'csv') {
            return $this->transactionCsv($orders);
        }

        return Inertia::render('reports/transactions', [
            'rows' => $orders,
            'filters' => $request->only([
                'outlet_id',
                'date_from',
                'date_to',
                'payment_method',
                'payment_status',
                'order_status',
                'created_by',
            ]),
        ]);
    }

    public function revenue(Request $request, OrderStatusService $statusService): Response
    {
        $outletIds = $this->reportOutletIds($request, $statusService);
        $orders = $this->transactionQuery($request, $outletIds)->where('payment_status', 'paid');
        $totalRevenue = (float) (clone $orders)->sum('grand_total');
        $totalPaidOrders = (clone $orders)->count();

        return Inertia::render('reports/revenue', [
            'metrics' => [
                'total_revenue' => $totalRevenue,
                'total_cash_revenue' => (float) Payment::query()->whereIn('outlet_id', $outletIds)->where('method', 'cash')->where('status', 'paid')->sum('amount'),
                'total_qris_revenue' => (float) Payment::query()->whereIn('outlet_id', $outletIds)->where('method', 'qris')->where('status', 'paid')->sum('amount'),
                'total_orders_paid' => $totalPaidOrders,
                'average_order_value' => $totalPaidOrders > 0 ? $totalRevenue / $totalPaidOrders : 0,
            ],
        ]);
    }

    public function services(Request $request, OrderStatusService $statusService): Response
    {
        $outletIds = $this->reportOutletIds($request, $statusService);

        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.outlet_id', $outletIds)
            ->where('orders.payment_status', 'paid')
            ->selectRaw('
                order_items.service_name,
                order_items.variant_name,
                sum(order_items.quantity) as total_quantity,
                sum(order_items.charged_quantity) as total_charged_quantity,
                count(distinct order_items.order_id) as total_orders,
                sum(order_items.subtotal) as total_revenue
            ')
            ->groupBy('order_items.service_name', 'order_items.variant_name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(fn ($row) => [
                'service_name' => $row->service_name,
                'variant_name' => $row->variant_name,
                'total_quantity' => (float) $row->total_quantity,
                'total_charged_quantity' => (float) $row->total_charged_quantity,
                'total_orders' => (int) $row->total_orders,
                'total_revenue' => (float) $row->total_revenue,
            ]);

        return Inertia::render('reports/services', [
            'rows' => $rows,
        ]);
    }

    public function customers(Request $request, OrderStatusService $statusService): Response
    {
        $outletIds = $this->reportOutletIds($request, $statusService);

        return Inertia::render('reports/customers', [
            'rows' => Customer::query()
                ->whereIn('outlet_id', $outletIds)
                ->withMax('orders', 'order_date')
                ->orderByDesc('total_spent')
                ->get(['id', 'name', 'phone', 'total_orders', 'total_spent']),
        ]);
    }

    /**
     * @param  array<int>  $outletIds
     */
    private function transactionQuery(Request $request, array $outletIds): Builder
    {
        return Order::query()
            ->whereIn('outlet_id', $outletIds)
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->string('payment_status')->toString()))
            ->when($request->filled('order_status'), fn ($query) => $query->where('order_status', $request->string('order_status')->toString()))
            ->when($request->filled('created_by'), fn ($query) => $query->where('created_by', $request->integer('created_by')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('order_date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('order_date', '<=', $request->date('date_to')))
            ->when($request->filled('payment_method'), function ($query) use ($request) {
                $query->whereHas('activePayment', fn ($query) => $query->where('method', $request->string('payment_method')->toString()));
            });
    }

    /**
     * @return array<int>
     */
    private function reportOutletIds(Request $request, OrderStatusService $statusService): array
    {
        $user = $request->user();
        abort_unless($statusService->canManageReports($user), 403);

        $accessibleOutletIds = OutletAccess::accessibleOutletIds($user);
        $outletId = $request->integer('outlet_id') ?: null;

        if ($outletId !== null) {
            abort_unless(in_array($outletId, $accessibleOutletIds, true), 403);

            return [$outletId];
        }

        return $accessibleOutletIds;
    }

    private function transactionCsv($orders)
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, [
            'invoice_number',
            'outlet',
            'customer',
            'phone',
            'order_date',
            'payment_method',
            'payment_status',
            'order_status',
            'subtotal',
            'discount',
            'additional_fee',
            'delivery_fee',
            'grand_total',
            'paid_at',
            'created_by',
        ]);

        foreach ($orders as $order) {
            fputcsv($handle, [
                $order->invoice_number,
                $order->outlet?->name,
                $order->customer?->name,
                $order->customer?->phone,
                $order->order_date?->toDateTimeString(),
                $order->activePayment?->method,
                $order->payment_status,
                $order->order_status,
                $order->subtotal,
                $order->discount_amount,
                $order->additional_fee,
                $order->delivery_fee,
                $order->grand_total,
                $order->activePayment?->paid_at?->toDateTimeString(),
                $order->creator?->name,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="transaction-report.csv"',
        ]);
    }
}
