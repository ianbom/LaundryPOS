<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\BusinessSettings;
use Inertia\Inertia;
use Inertia\Response;

class PublicInvoiceController extends Controller
{
    public function show(string $trackingToken): Response
    {
        $order = Order::query()
            ->where('tracking_token', $trackingToken)
            ->with([
                'customer:id,name,phone',
                'outlet:id,name,phone,whatsapp_number,address',
                'items:id,order_id,service_name,variant_name,pricing_type,unit,quantity,charged_quantity,unit_price,subtotal',
                'activePayment:id,order_id,method,status,amount,amount_paid,change_amount,paid_at',
            ])
            ->firstOrFail();

        $settings = BusinessSettings::current();

        return Inertia::render('public/invoice', [
            'invoice' => [
                'business' => [
                    'name' => $settings->business_name,
                    'logo_path' => $settings->logo_path,
                    'receipt_footer_text' => $settings->receipt_footer_text,
                    'terms_and_conditions' => $settings->terms_and_conditions,
                ],
                'outlet' => [
                    'name' => $order->outlet->name,
                    'address' => $order->outlet->address,
                    'phone' => $order->outlet->phone,
                    'whatsapp_number' => $order->outlet->whatsapp_number,
                ],
                'customer' => [
                    'name' => $order->customer->name,
                    'phone' => $order->customer->phone,
                ],
                'order' => [
                    'invoice_number' => $order->invoice_number,
                    'order_date' => $order->order_date,
                    'subtotal' => $order->subtotal,
                    'discount_amount' => $order->discount_amount,
                    'additional_fee' => $order->additional_fee,
                    'delivery_fee' => $order->delivery_fee,
                    'grand_total' => $order->grand_total,
                    'payment_status' => $order->payment_status,
                    'tracking_token' => $order->tracking_token,
                ],
                'payment' => $order->activePayment ? [
                    'method' => $order->activePayment->method,
                    'status' => $order->activePayment->status,
                    'paid_at' => $order->activePayment->paid_at,
                ] : null,
                'items' => $order->items,
                'tracking_url' => url("/track/{$order->tracking_token}"),
            ],
        ]);
    }
}
