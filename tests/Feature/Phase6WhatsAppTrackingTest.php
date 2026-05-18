<?php

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserOutlet;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use App\Services\WhatsApp\FonnteWhatsAppService;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;

test('fonnte service normalizes phone sends message and logs response', function () {
    $order = phase6Order(paymentStatus: 'paid');

    BusinessSetting::query()->create([
        'business_name' => 'Bersih Laundry',
        'whatsapp_provider' => 'fonnte',
        'whatsapp_api_key' => 'secret-token',
    ]);
    WhatsappTemplate::query()->create([
        'outlet_id' => $order->outlet_id,
        'type' => 'payment_receipt',
        'title' => 'Receipt',
        'body' => 'Halo {customer_name}, invoice {invoice_number}, total {grand_total}, tracking {tracking_url}',
        'is_active' => true,
    ]);
    Http::fake([
        'api.fonnte.com/send' => Http::response([
            'status' => true,
            'id' => ['msg-123'],
        ]),
    ]);

    $message = app(FonnteWhatsAppService::class)->sendPaymentReceipt($order);

    expect($message)->not->toBeNull()
        ->and($message->fresh()->status)->toBe('sent')
        ->and($message->fresh()->phone)->toBe('6281234567890')
        ->and($message->fresh()->provider_message_id)->toBe('msg-123');

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'secret-token')
        && $request['target'] === '6281234567890'
        && str_contains($request['message'], $order->invoice_number));
});

test('fonnte failure is logged without throwing', function () {
    $order = phase6Order(paymentStatus: 'paid');
    BusinessSetting::query()->create([
        'business_name' => 'Bersih Laundry',
        'whatsapp_provider' => 'fonnte',
        'whatsapp_api_key' => 'secret-token',
    ]);
    Http::fake([
        'api.fonnte.com/send' => Http::response([
            'status' => false,
            'reason' => 'Invalid token',
        ], 200),
    ]);

    $message = app(FonnteWhatsAppService::class)->sendPaymentReceipt($order);

    expect($message)->not->toBeNull()
        ->and($message->fresh()->status)->toBe('failed')
        ->and($message->fresh()->error_message)->toBe('Invalid token');
});

test('order ready automatic message is not duplicated after sent log exists', function () {
    $order = phase6Order(status: 'ready_to_pickup', paymentStatus: 'paid');
    WhatsappMessage::query()->create([
        'outlet_id' => $order->outlet_id,
        'order_id' => $order->id,
        'customer_id' => $order->customer_id,
        'provider' => 'fonnte',
        'phone' => '6281234567890',
        'message_type' => 'order_ready',
        'message_body' => 'sent',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $message = app(FonnteWhatsAppService::class)->sendOrderReady($order);

    expect($message)->toBeNull()
        ->and(WhatsappMessage::query()->where('message_type', 'order_ready')->count())->toBe(1);
});

test('public tracking page uses token and hides internal data', function () {
    $order = phase6Order();

    $this->get('/track/'.$order->tracking_token)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/tracking')
            ->where('tracking.order.invoice_number', $order->invoice_number)
            ->where('tracking.customer.name', 'Budi Santoso')
            ->missing('tracking.order.internal_notes')
            ->missing('tracking.customer.address')
        );

    $this->get('/track/'.$order->id)->assertNotFound();
});

test('public invoice page exposes printable invoice by tracking token', function () {
    $order = phase6Order(paymentStatus: 'paid');

    $this->get('/public/invoice/'.$order->tracking_token)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('public/invoice')
            ->where('invoice.order.invoice_number', $order->invoice_number)
            ->where('invoice.payment.method', 'cash')
            ->where('invoice.tracking_url', url('/track/'.$order->tracking_token))
        );
});

test('authenticated user can manually resend payment receipt', function () {
    $order = phase6Order(paymentStatus: 'paid');
    $user = staffUserWithOrderAccess($order->outlet);
    BusinessSetting::query()->create([
        'business_name' => 'Bersih Laundry',
        'whatsapp_provider' => 'fonnte',
        'whatsapp_api_key' => 'secret-token',
    ]);
    Http::fake([
        'api.fonnte.com/send' => Http::response(['status' => true, 'id' => ['manual-1']]),
    ]);

    $this->actingAs($user)
        ->post("/orders/{$order->id}/whatsapp/payment-receipt")
        ->assertRedirect();

    expect(WhatsappMessage::query()->where('message_type', 'payment_receipt')->where('status', 'sent')->exists())
        ->toBeTrue();
});

function phase6Order(string $status = 'processing', string $paymentStatus = 'unpaid'): Order
{
    $outlet = Outlet::query()->create([
        'name' => 'Pusat',
        'slug' => 'pusat',
        'phone' => '031123456',
        'whatsapp_number' => '081234567890',
        'address' => 'Jl. Laundry No. 1',
    ]);
    $customer = Customer::query()->create([
        'outlet_id' => $outlet->id,
        'name' => 'Budi Santoso',
        'phone' => '081234567890',
        'whatsapp_number' => '081234567890',
        'address' => 'Alamat privat',
    ]);
    $order = Order::query()->create([
        'outlet_id' => $outlet->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'LDR-SBY-20260518-0001',
        'order_status' => $status,
        'payment_status' => $paymentStatus,
        'order_date' => now(),
        'estimated_done_at' => now()->addDay(),
        'subtotal' => 75000,
        'discount_amount' => 0,
        'additional_fee' => 0,
        'delivery_fee' => 0,
        'grand_total' => 75000,
        'customer_notes' => 'Catatan customer',
        'internal_notes' => 'Catatan internal',
        'tracking_token' => 'trk_phase6_token',
    ]);
    OrderItem::query()->create([
        'order_id' => $order->id,
        'service_name' => 'Cuci Kering Setrika',
        'variant_name' => '2x24 Jam',
        'pricing_type' => 'per_kg',
        'unit' => 'kg',
        'quantity' => 5,
        'charged_quantity' => 5,
        'unit_price' => 15000,
        'subtotal' => 75000,
    ]);
    OrderStatusHistory::query()->create([
        'order_id' => $order->id,
        'new_status' => 'processing',
        'notes' => 'Sedang diproses',
        'created_at' => now(),
    ]);

    if ($paymentStatus === 'paid') {
        $payment = Payment::query()->create([
            'outlet_id' => $outlet->id,
            'order_id' => $order->id,
            'provider' => 'manual',
            'method' => 'cash',
            'status' => 'paid',
            'is_active' => true,
            'amount' => 75000,
            'amount_paid' => 100000,
            'change_amount' => 25000,
            'paid_at' => now(),
        ]);
        $order->forceFill(['active_payment_id' => $payment->id])->save();
    }

    return $order->fresh(['outlet', 'customer', 'activePayment']);
}

function staffUserWithOrderAccess(Outlet $outlet)
{
    $user = User::query()->create([
        'name' => 'Kasir',
        'email' => 'kasir@example.com',
        'password' => 'password',
        'global_role' => 'staff',
        'is_active' => true,
    ]);
    UserOutlet::query()->create([
        'user_id' => $user->id,
        'outlet_id' => $outlet->id,
        'role' => 'cashier',
        'can_manage_orders' => true,
        'can_manage_payments' => true,
        'is_primary' => true,
        'is_active' => true,
    ]);

    return $user;
}
