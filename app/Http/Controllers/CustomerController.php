<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\Outlet;
use App\Services\ActivityLogger;
use App\Support\OutletAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $outletIds = OutletAccess::accessibleOutletIds($request->user());

        return Inertia::render('customers/index', [
            'customers' => Customer::query()
                ->with('outlet:id,name')
                ->whereIn('outlet_id', $outletIds)
                ->when($request->string('search')->toString(), function ($query, string $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('whatsapp_number', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'outlets' => Outlet::query()->whereIn('id', $outletIds)->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only('search'),
        ]);
    }

    public function create(Request $request): Response
    {
        $outletIds = OutletAccess::accessibleOutletIds($request->user());

        return Inertia::render('customers/form', [
            'customer' => null,
            'outlets' => Outlet::query()->whereIn('id', $outletIds)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreCustomerRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $customer = Customer::query()->create($request->validated());
        $logger->log($request, 'customer_created', $customer, $customer->outlet_id, null, $customer->toArray());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Customer created.']);

        return redirect('/customers');
    }

    public function edit(Request $request, Customer $customer): Response
    {
        abort_unless(OutletAccess::canManageOrders($request->user(), (int) $customer->outlet_id), 403);
        $outletIds = OutletAccess::accessibleOutletIds($request->user());

        return Inertia::render('customers/form', [
            'customer' => $customer,
            'outlets' => Outlet::query()->whereIn('id', $outletIds)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer, ActivityLogger $logger): RedirectResponse
    {
        $old = $customer->getOriginal();
        $customer->update($request->validated());
        $logger->log($request, 'customer_updated', $customer, $customer->outlet_id, $old, $customer->fresh()->toArray());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Customer updated.']);

        return redirect('/customers');
    }

    public function destroy(Request $request, Customer $customer, ActivityLogger $logger): RedirectResponse
    {
        abort_unless(OutletAccess::canManageOrders($request->user(), (int) $customer->outlet_id), 403);
        $customer->delete();
        $logger->log($request, 'customer_deleted', $customer, $customer->outlet_id);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Customer deleted.']);

        return redirect('/customers');
    }
}
