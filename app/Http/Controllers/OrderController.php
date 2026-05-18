<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Outlet;
use App\Support\BusinessSettings;
use App\Support\OutletAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $outletIds = OutletAccess::accessibleOutletIds($request->user());

        return Inertia::render('orders/index', [
            'orders' => Order::query()
                ->with(['outlet:id,name', 'customer:id,name,phone,whatsapp_number', 'creator:id,name', 'activePayment:id,order_id,method,provider,status'])
                ->whereIn('outlet_id', $outletIds)
                ->when($request->filled('outlet_id'), fn ($query) => $query->where('outlet_id', $request->integer('outlet_id')))
                ->when($request->filled('order_status'), fn ($query) => $query->where('order_status', $request->input('order_status')))
                ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->input('payment_status')))
                ->when($request->filled('payment_method'), fn ($query) => $query->whereHas('activePayment', fn ($query) => $query->where('method', $request->input('payment_method'))))
                ->when($request->filled('created_by'), fn ($query) => $query->where('created_by', $request->integer('created_by')))
                ->when($request->filled('date_from'), fn ($query) => $query->whereDate('order_date', '>=', $request->date('date_from')))
                ->when($request->filled('date_to'), fn ($query) => $query->whereDate('order_date', '<=', $request->date('date_to')))
                ->when($request->string('search')->toString(), function ($query, string $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('invoice_number', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%")
                                    ->orWhere('whatsapp_number', 'like', "%{$search}%");
                            });
                    });
                })
                ->latest('order_date')
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'outlets' => Outlet::query()->whereIn('id', $outletIds)->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'outlet_id', 'order_status', 'payment_status', 'payment_method', 'date_from', 'date_to', 'created_by']),
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        abort_unless(OutletAccess::canAccessOutlet($request->user(), (int) $order->outlet_id), 403);

        return Inertia::render('orders/show', [
            'order' => $order->load([
                'outlet',
                'customer',
                'creator:id,name',
                'items',
                'activePayment.confirmedBy:id,name',
                'payments.confirmedBy:id,name',
                'statusHistories' => fn ($query) => $query->with('changedBy:id,name')->oldest(),
                'whatsappMessages' => fn ($query) => $query->latest(),
            ]),
        ]);
    }

    public function invoice(Request $request, Order $order): Response
    {
        abort_unless(OutletAccess::canAccessOutlet($request->user(), (int) $order->outlet_id), 403);

        return Inertia::render('orders/invoice', [
            'order' => $order->load(['outlet', 'customer', 'items', 'activePayment']),
            'businessSettings' => BusinessSettings::current(),
            'trackingUrl' => url('/track/'.$order->tracking_token),
        ]);
    }
}
