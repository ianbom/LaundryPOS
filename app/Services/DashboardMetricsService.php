<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardMetricsService
{
    /**
     * @param  array<int>  $outletIds
     * @return array<string, mixed>
     */
    public function metrics(array $outletIds, CarbonImmutable $date): array
    {
        $ordersToday = $this->baseOrderQuery($outletIds)
            ->whereDate('order_date', $date)
            ->count();

        $paidToday = $this->baseOrderQuery($outletIds)
            ->where('payment_status', 'paid')
            ->whereDate('order_date', $date);

        $revenueToday = (float) $paidToday->sum('grand_total');

        return [
            'revenueToday' => $revenueToday,
            'ordersToday' => $ordersToday,
            'pendingPaymentOrders' => $this->baseOrderQuery($outletIds)->where('payment_status', 'pending')->count(),
            'processingOrders' => $this->baseOrderQuery($outletIds)->where('order_status', 'processing')->count(),
            'readyToPickupOrders' => $this->baseOrderQuery($outletIds)->where('order_status', 'ready_to_pickup')->count(),
            'completedOrdersToday' => $this->baseOrderQuery($outletIds)->where('order_status', 'completed')->whereDate('order_date', $date)->count(),
            'cashRevenueToday' => (float) Payment::query()
                ->whereIn('outlet_id', $outletIds)
                ->where('method', 'cash')
                ->where('status', 'paid')
                ->whereDate('paid_at', $date)
                ->sum('amount'),
            'qrisRevenueToday' => (float) Payment::query()
                ->whereIn('outlet_id', $outletIds)
                ->where('method', 'qris')
                ->where('status', 'paid')
                ->whereDate('paid_at', $date)
                ->sum('amount'),
        ];
    }

    /**
     * @param  array<int>  $outletIds
     * @return array<int, array<string, mixed>>
     */
    public function revenueChart(array $outletIds, int $days = 7): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = $this->baseOrderQuery($outletIds)
            ->where('payment_status', 'paid')
            ->whereDate('order_date', '>=', $start)
            ->selectRaw('date(order_date) as date, sum(grand_total) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($rows, $start) {
                $date = $start->copy()->addDays($offset)->toDateString();

                return [
                    'date' => $date,
                    'total' => (float) ($rows[$date] ?? 0),
                ];
            })
            ->all();
    }

    /**
     * @param  array<int>  $outletIds
     * @return array<string, int>
     */
    public function orderStatusDistribution(array $outletIds): array
    {
        return $this->baseOrderQuery($outletIds)
            ->selectRaw('order_status, count(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /**
     * @param  array<int>  $outletIds
     * @return array<string, int>
     */
    public function paymentMethodDistribution(array $outletIds): array
    {
        return Payment::query()
            ->whereIn('outlet_id', $outletIds)
            ->where('status', 'paid')
            ->selectRaw('method, count(*) as total')
            ->groupBy('method')
            ->pluck('total', 'method')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /**
     * @param  array<int>  $outletIds
     * @return Collection<int, Order>
     */
    public function recentOrders(array $outletIds): Collection
    {
        return $this->baseOrderQuery($outletIds)
            ->with([
                'activePayment:id,order_id,method,status,paid_at',
                'customer:id,name,phone',
                'items:id,order_id,service_name,charged_quantity,unit',
                'outlet:id,name',
            ])
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * @param  array<int>  $outletIds
     */
    private function baseOrderQuery(array $outletIds): Builder
    {
        return Order::query()->whereIn('outlet_id', $outletIds);
    }
}
