<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceCategoryRequest;
use App\Http\Requests\UpdateServiceCategoryRequest;
use App\Models\Outlet;
use App\Models\ServiceCategory;
use App\Services\ActivityLogger;
use App\Support\OutletAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $outletIds = OutletAccess::accessibleOutletIds($request->user());

        return Inertia::render('service-categories/index', [
            'categories' => ServiceCategory::query()
                ->with('outlet:id,name')
                ->withCount('services')
                ->whereIn('outlet_id', $outletIds)
                ->when($request->filled('outlet_id'), fn ($query) => $query->where('outlet_id', $request->integer('outlet_id')))
                ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->input('status') === 'active'))
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
            'outlets' => Outlet::query()->whereIn('id', $outletIds)->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'outlet_id', 'status']),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('service-categories/form', [
            'category' => null,
            'outlets' => Outlet::query()->whereIn('id', OutletAccess::accessibleOutletIds($request->user()))->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreServiceCategoryRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $category = ServiceCategory::query()->create($request->safe()->merge([
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order'),
        ])->all());
        $logger->log($request, 'service_category_created', $category, $category->outlet_id, null, $category->toArray());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service category created.']);

        return redirect('/service-categories');
    }

    public function edit(Request $request, ServiceCategory $serviceCategory): Response
    {
        abort_unless(OutletAccess::canManageServices($request->user(), (int) $serviceCategory->outlet_id), 403);

        return Inertia::render('service-categories/form', [
            'category' => $serviceCategory,
            'outlets' => Outlet::query()->whereIn('id', OutletAccess::accessibleOutletIds($request->user()))->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory, ActivityLogger $logger): RedirectResponse
    {
        $old = $serviceCategory->getOriginal();
        $serviceCategory->update($request->safe()->merge([
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->integer('sort_order'),
        ])->all());
        $logger->log($request, 'service_category_updated', $serviceCategory, $serviceCategory->outlet_id, $old, $serviceCategory->fresh()->toArray());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service category updated.']);

        return redirect('/service-categories');
    }

    public function destroy(Request $request, ServiceCategory $serviceCategory, ActivityLogger $logger): RedirectResponse
    {
        abort_unless(OutletAccess::canManageServices($request->user(), (int) $serviceCategory->outlet_id), 403);
        $serviceCategory->delete();
        $logger->log($request, 'service_category_deleted', $serviceCategory, $serviceCategory->outlet_id);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service category deleted.']);

        return redirect('/service-categories');
    }

    public function toggleActive(Request $request, ServiceCategory $serviceCategory, ActivityLogger $logger): RedirectResponse
    {
        abort_unless(OutletAccess::canManageServices($request->user(), (int) $serviceCategory->outlet_id), 403);
        $serviceCategory->update(['is_active' => ! $serviceCategory->is_active]);
        $logger->log($request, 'service_category_toggled', $serviceCategory, $serviceCategory->outlet_id);

        return redirect('/service-categories');
    }
}
