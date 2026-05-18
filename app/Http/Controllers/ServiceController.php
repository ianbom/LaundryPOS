<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Outlet;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ActivityLogger;
use App\Support\OutletAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $outletIds = OutletAccess::accessibleOutletIds($request->user());

        return Inertia::render('services/index', [
            'services' => Service::query()
                ->with(['outlet:id,name', 'serviceCategory:id,name'])
                ->withCount('variants')
                ->whereIn('outlet_id', $outletIds)
                ->when($request->filled('outlet_id'), fn ($query) => $query->where('outlet_id', $request->integer('outlet_id')))
                ->when($request->filled('service_category_id'), fn ($query) => $query->where('service_category_id', $request->integer('service_category_id')))
                ->when($request->filled('pricing_type'), fn ($query) => $query->where('pricing_type', $request->input('pricing_type')))
                ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->input('status') === 'active'))
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
            'outlets' => Outlet::query()->whereIn('id', $outletIds)->orderBy('name')->get(['id', 'name']),
            'categories' => ServiceCategory::query()->whereIn('outlet_id', $outletIds)->orderBy('name')->get(['id', 'outlet_id', 'name']),
            'filters' => $request->only(['search', 'outlet_id', 'service_category_id', 'pricing_type', 'status']),
        ]);
    }

    public function create(Request $request): Response
    {
        $outletIds = OutletAccess::accessibleOutletIds($request->user());

        return Inertia::render('services/form', [
            'service' => null,
            'outlets' => Outlet::query()->whereIn('id', $outletIds)->orderBy('name')->get(['id', 'name']),
            'categories' => ServiceCategory::query()->whereIn('outlet_id', $outletIds)->orderBy('name')->get(['id', 'outlet_id', 'name']),
        ]);
    }

    public function store(StoreServiceRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $service = Service::query()->create($request->safe()->merge([
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order'),
        ])->all());
        $logger->log($request, 'service_created', $service, $service->outlet_id, null, $service->toArray());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service created.']);

        return redirect('/services');
    }

    public function show(Request $request, Service $service): Response
    {
        abort_unless(OutletAccess::canManageServices($request->user(), (int) $service->outlet_id), 403);

        return Inertia::render('services/show', [
            'service' => $service->load(['outlet:id,name', 'serviceCategory:id,name', 'variants' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')]),
        ]);
    }

    public function edit(Request $request, Service $service): Response
    {
        abort_unless(OutletAccess::canManageServices($request->user(), (int) $service->outlet_id), 403);
        $outletIds = OutletAccess::accessibleOutletIds($request->user());

        return Inertia::render('services/form', [
            'service' => $service,
            'outlets' => Outlet::query()->whereIn('id', $outletIds)->orderBy('name')->get(['id', 'name']),
            'categories' => ServiceCategory::query()->whereIn('outlet_id', $outletIds)->orderBy('name')->get(['id', 'outlet_id', 'name']),
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service, ActivityLogger $logger): RedirectResponse
    {
        $old = $service->getOriginal();
        $service->update($request->safe()->merge([
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->integer('sort_order'),
        ])->all());
        $logger->log($request, 'service_updated', $service, $service->outlet_id, $old, $service->fresh()->toArray());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service updated.']);

        return redirect('/services');
    }

    public function destroy(Request $request, Service $service, ActivityLogger $logger): RedirectResponse
    {
        abort_unless(OutletAccess::canManageServices($request->user(), (int) $service->outlet_id), 403);
        $service->delete();
        $logger->log($request, 'service_deleted', $service, $service->outlet_id);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service deleted.']);

        return redirect('/services');
    }

    public function toggleActive(Request $request, Service $service, ActivityLogger $logger): RedirectResponse
    {
        abort_unless(OutletAccess::canManageServices($request->user(), (int) $service->outlet_id), 403);
        $service->update(['is_active' => ! $service->is_active]);
        $logger->log($request, 'service_toggled', $service, $service->outlet_id);

        return redirect('/services');
    }
}
