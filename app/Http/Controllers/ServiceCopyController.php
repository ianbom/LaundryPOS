<?php

namespace App\Http\Controllers;

use App\Http\Requests\CopyServicesRequest;
use App\Models\Outlet;
use App\Models\ServiceCategory;
use App\Services\ActivityLogger;
use App\Services\ServiceCatalogCopyService;
use App\Support\OutletAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceCopyController extends Controller
{
    public function create(Request $request): Response
    {
        $outletIds = OutletAccess::accessibleOutletIds($request->user());

        return Inertia::render('services/copy', [
            'outlets' => Outlet::query()->whereIn('id', $outletIds)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'preview' => null,
            'result' => $request->session()->get('copy_result'),
        ]);
    }

    public function store(CopyServicesRequest $request, ServiceCatalogCopyService $copyService, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validated();
        $result = $copyService->copy(
            $data['source_outlet_id'],
            $data['target_outlet_id'],
            $data['copy_mode'],
            $request->boolean('include_inactive'),
        );

        $logger->log($request, 'services_copied', null, $data['target_outlet_id'], null, [
            ...$data,
            ...$result,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service catalog copied.']);

        return redirect('/services/copy')->with('copy_result', $result);
    }

    public function preview(Request $request): array
    {
        $request->validate([
            'source_outlet_id' => ['required', 'exists:outlets,id'],
            'include_inactive' => ['boolean'],
        ]);

        abort_unless(OutletAccess::canAccessOutlet($request->user(), $request->integer('source_outlet_id')), 403);

        $categories = ServiceCategory::query()
            ->withCount('services')
            ->where('outlet_id', $request->integer('source_outlet_id'))
            ->when(! $request->boolean('include_inactive'), fn ($query) => $query->where('is_active', true))
            ->get();

        return [
            'categories' => $categories->count(),
            'services' => $categories->sum('services_count'),
        ];
    }
}
