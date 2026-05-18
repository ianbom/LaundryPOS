<?php

namespace App\Http\Controllers;

use App\Services\DashboardMetricsService;
use App\Support\OutletAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardMetricsService $metrics): Response
    {
        $outletIds = $this->resolveOutletIds($request);

        return Inertia::render('dashboard', [
            'metrics' => $metrics->metrics($outletIds, now()->toImmutable()),
            'revenueChart' => $metrics->revenueChart($outletIds, 7),
            'orderStatusDistribution' => $metrics->orderStatusDistribution($outletIds),
            'paymentMethodDistribution' => $metrics->paymentMethodDistribution($outletIds),
            'recentOrders' => $metrics->recentOrders($outletIds),
            'filters' => [
                'outlet_id' => $request->integer('outlet_id') ?: null,
                'date_range' => $request->string('date_range', 'today')->toString(),
            ],
        ]);
    }

    /**
     * @return array<int>
     */
    private function resolveOutletIds(Request $request): array
    {
        $user = $request->user();
        $accessibleOutletIds = OutletAccess::accessibleOutletIds($user);
        $requestedOutletId = $request->integer('outlet_id') ?: null;

        if ($requestedOutletId !== null) {
            abort_unless(in_array($requestedOutletId, $accessibleOutletIds, true), 403);

            return [$requestedOutletId];
        }

        if ($user->global_role === 'owner' && $request->string('outlet_id')->toString() === 'all') {
            return $accessibleOutletIds;
        }

        $activeOutletId = OutletAccess::activeOutletId($user);

        return $activeOutletId !== null ? [$activeOutletId] : $accessibleOutletIds;
    }
}
