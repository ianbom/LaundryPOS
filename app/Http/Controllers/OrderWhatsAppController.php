<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendCustomOrderWhatsAppRequest;
use App\Models\Order;
use App\Services\ActivityLogger;
use App\Services\WhatsApp\FonnteWhatsAppService;
use App\Support\OutletAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderWhatsAppController extends Controller
{
    public function paymentReceipt(Request $request, Order $order, FonnteWhatsAppService $whatsApp, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $order);

        abort_unless($order->payment_status === 'paid', 422, 'Payment receipt can only be sent for paid orders.');

        $message = $whatsApp->sendPaymentReceipt($order);
        $logger->log($request, 'whatsapp_payment_receipt_sent', $message, $order->outlet_id);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Payment receipt WhatsApp queued.']);

        return back();
    }

    public function orderReady(Request $request, Order $order, FonnteWhatsAppService $whatsApp, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $order);

        $message = $whatsApp->sendOrderReady($order, manual: true);
        $logger->log($request, 'whatsapp_order_ready_sent', $message, $order->outlet_id);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Ready notification WhatsApp queued.']);

        return back();
    }

    public function orderCompleted(Request $request, Order $order, FonnteWhatsAppService $whatsApp, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $order);

        $message = $whatsApp->sendOrderCompleted($order, manual: true);
        $logger->log($request, 'whatsapp_order_completed_sent', $message, $order->outlet_id);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Completed notification WhatsApp queued.']);

        return back();
    }

    public function custom(SendCustomOrderWhatsAppRequest $request, Order $order, FonnteWhatsAppService $whatsApp, ActivityLogger $logger): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $order);

        $message = $whatsApp->sendCustomOrderMessage($order, $request->string('message')->toString());
        $logger->log($request, 'whatsapp_custom_sent', $message, $order->outlet_id);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Custom WhatsApp queued.']);

        return back();
    }

    private function authorizeOrderAccess(Request $request, Order $order): void
    {
        abort_unless(OutletAccess::canManageOrders($request->user(), $order->outlet_id), 403);
    }
}
