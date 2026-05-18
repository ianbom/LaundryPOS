<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\BusinessSettings;
use Inertia\Inertia;
use Inertia\Response;

class PublicTrackingController extends Controller
{
    public function show(string $trackingToken): Response
    {
        $order = Order::query()
            ->where('tracking_token', $trackingToken)
            ->with([
                'customer:id,name,phone,whatsapp_number',
                'outlet:id,name,phone,whatsapp_number,address',
                'items:id,order_id,service_name,variant_name,pricing_type,unit,quantity,charged_quantity,unit_price,subtotal,notes',
                'statusHistories' => fn ($query) => $query->select(['id', 'order_id', 'new_status', 'notes', 'created_at'])->orderBy('created_at'),
            ])
            ->firstOrFail();

        $settings = BusinessSettings::current();

        return Inertia::render('public/tracking', [
            'tracking' => [
                'business' => [
                    'name' => $settings->business_name,
                    'logo_path' => $settings->logo_path,
                ],
                'outlet' => [
                    'name' => $order->outlet->name,
                    'address' => $order->outlet->address,
                    'phone' => $order->outlet->phone,
                    'whatsapp_number' => $order->outlet->whatsapp_number,
                ],
                'customer' => [
                    'name' => $order->customer->name,
                ],
                'order' => [
                    'invoice_number' => $order->invoice_number,
                    'order_date' => $order->order_date,
                    'estimated_done_at' => $order->estimated_done_at,
                    'order_status' => $order->order_status,
                    'payment_status' => $order->payment_status,
                    'grand_total' => $order->grand_total,
                    'tracking_token' => $order->tracking_token,
                ],
                'items' => $order->items,
                'timeline' => $order->statusHistories,
            ],
        ]);
    }
}
