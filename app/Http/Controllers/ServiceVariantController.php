<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceVariantRequest;
use App\Http\Requests\UpdateServiceVariantRequest;
use App\Models\Service;
use App\Models\ServiceVariant;
use App\Services\ActivityLogger;
use App\Support\OutletAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceVariantController extends Controller
{
    public function index(Request $request, Service $service): Response
    {
        abort_unless(OutletAccess::canManageServices($request->user(), (int) $service->outlet_id), 403);

        return Inertia::render('service-variants/index', [
            'service' => $service->load('serviceCategory:id,name', 'outlet:id,name'),
            'variants' => $service->variants()
                ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->input('status') === 'active'))
                ->when($request->string('search')->toString(), fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(Request $request, Service $service): Response
    {
        abort_unless(OutletAccess::canManageServices($request->user(), (int) $service->outlet_id), 403);

        return Inertia::render('service-variants/form', [
            'service' => $service,
            'variant' => null,
        ]);
    }

    public function store(StoreServiceVariantRequest $request, Service $service, ActivityLogger $logger): RedirectResponse
    {
        $variant = $service->variants()->create($request->safe()->merge([
            'outlet_id' => $service->outlet_id,
            'is_express' => $request->boolean('is_express'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order'),
        ])->all());
        $logger->log($request, 'service_variant_created', $variant, $variant->outlet_id, null, $variant->toArray());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service variant created.']);

        return redirect("/services/{$service->id}/variants");
    }

    public function edit(Request $request, Service $service, ServiceVariant $variant): Response
    {
        abort_unless($variant->service_id === $service->id, 404);
        abort_unless(OutletAccess::canManageServices($request->user(), (int) $service->outlet_id), 403);

        return Inertia::render('service-variants/form', [
            'service' => $service,
            'variant' => $variant,
        ]);
    }

    public function update(UpdateServiceVariantRequest $request, Service $service, ServiceVariant $variant, ActivityLogger $logger): RedirectResponse
    {
        $old = $variant->getOriginal();
        $variant->update($request->safe()->merge([
            'outlet_id' => $service->outlet_id,
            'is_express' => $request->boolean('is_express'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->integer('sort_order'),
        ])->all());
        $logger->log($request, 'service_variant_updated', $variant, $variant->outlet_id, $old, $variant->fresh()->toArray());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service variant updated.']);

        return redirect("/services/{$service->id}/variants");
    }

    public function destroy(Request $request, Service $service, ServiceVariant $variant, ActivityLogger $logger): RedirectResponse
    {
        abort_unless($variant->service_id === $service->id, 404);
        abort_unless(OutletAccess::canManageServices($request->user(), (int) $service->outlet_id), 403);
        $variant->delete();
        $logger->log($request, 'service_variant_deleted', $variant, $variant->outlet_id);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Service variant deleted.']);

        return redirect("/services/{$service->id}/variants");
    }

    public function toggleActive(Request $request, Service $service, ServiceVariant $variant, ActivityLogger $logger): RedirectResponse
    {
        abort_unless($variant->service_id === $service->id, 404);
        abort_unless(OutletAccess::canManageServices($request->user(), (int) $service->outlet_id), 403);
        $variant->update(['is_active' => ! $variant->is_active]);
        $logger->log($request, 'service_variant_toggled', $variant, $variant->outlet_id);

        return redirect("/services/{$service->id}/variants");
    }
}
