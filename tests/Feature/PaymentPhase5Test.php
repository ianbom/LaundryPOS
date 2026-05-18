<?php

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserOutlet;
use Illuminate\Support\Facades\Http;

function paymentUser(Outlet $outlet, bool $canManagePayments = true): User
{
    $user = User::factory()->create([
        'global_role' => 'staff',
        'is_active' => true,
    ]);

    UserOutlet::query()->create([
        'user_id' => $user->id,
        'outlet_id' => $outlet->id,
        'role' => 'cashier',
        'can_manage_orders' => true,
        'can_manage_payments' => $canManagePayments,
        'is_primary' => true,
        'is_active' => true,
    ]);

    return $user;
}

function paymentOrder(array $attributes = []): Order
{
    $outlet = $attributes['outlet'] ?? Outlet::query()->create([
        'name' => 'Central',
        'slug' => 'central',
        'is_active' => true,
    ]);

    $customer = $attributes['customer'] ?? Customer::query()->create([
        'outlet_id' => $outlet->id,
        'name' => 'Budi Santoso',
        'phone' => '08123456789',
        'whatsapp_number' => '628123456789',
    ]);

    return Order::query()->create([
        'outlet_id' => $outlet->id,
        'customer_id' => $customer->id,
        'invoice_number' => $attributes['invoice_number'] ?? 'LDR-SBY-20260518-0001',
        'order_status' => $attributes['order_status'] ?? 'waiting_payment',
        'payment_status' => $attributes['payment_status'] ?? 'unpaid',
        'grand_total' => $attributes['grand_total'] ?? 25000,
        'tracking_token' => $attributes['tracking_token'] ?? 'trk_test_payment',
    ]);
}

function midtransSignature(string $orderId, string $statusCode, string $grossAmount, string $serverKey): string
{
    return hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
}

test('cash payment marks order paid and cancels active pending qris', function () {
    $order = paymentOrder();
    $user = paymentUser($order->outlet);
    $qris = Payment::query()->create([
        'outlet_id' => $order->outlet_id,
        'order_id' => $order->id,
        'provider' => 'midtrans',
        'method' => 'qris',
        'status' => 'pending',
        'is_active' => true,
        'amount' => $order->grand_total,
    ]);
    $order->forceFill([
        'active_payment_id' => $qris->id,
        'payment_status' => 'pending',
    ])->save();

    $this->actingAs($user)
        ->post(route('orders.payments.cash', $order), ['amount_paid' => 30000])
        ->assertRedirect();

    $order->refresh();
    $cash = Payment::query()->where('order_id', $order->id)->where('method', 'cash')->firstOrFail();

    expect($order->payment_status)->toBe('paid')
        ->and($order->order_status)->toBe('received')
        ->and((int) $order->active_payment_id)->toBe($cash->id)
        ->and($cash->status)->toBe('paid')
        ->and((float) $cash->change_amount)->toBe(5000.0)
        ->and($qris->refresh()->status)->toBe('cancelled');
});

test('cash payment rejects insufficient amount', function () {
    $order = paymentOrder();

    $this->actingAs(paymentUser($order->outlet))
        ->post(route('orders.payments.cash', $order), ['amount_paid' => 20000])
        ->assertStatus(422);

    expect($order->refresh()->payment_status)->toBe('unpaid')
        ->and(Payment::query()->where('order_id', $order->id)->doesntExist())->toBeTrue();
});

test('qris generation deactivates previous pending payment and stores midtrans response', function () {
    Http::fake([
        'api.sandbox.midtrans.com/v2/charge' => Http::response([
            'transaction_id' => 'trx-123',
            'reference_id' => 'ref-123',
            'actions' => [
                ['name' => 'generate-qr-code', 'url' => 'https://midtrans.test/qris.png'],
                ['name' => 'deeplink-redirect', 'url' => 'https://midtrans.test/pay'],
            ],
        ]),
    ]);

    BusinessSetting::query()->create([
        'business_name' => 'Bersih Laundry',
        'midtrans_server_key' => 'server-key',
        'qris_expiry_minutes' => 45,
    ]);

    $order = paymentOrder();
    $oldPayment = Payment::query()->create([
        'outlet_id' => $order->outlet_id,
        'order_id' => $order->id,
        'provider' => 'midtrans',
        'method' => 'qris',
        'status' => 'pending',
        'is_active' => true,
        'amount' => $order->grand_total,
    ]);
    $order->forceFill([
        'active_payment_id' => $oldPayment->id,
        'payment_status' => 'pending',
    ])->save();

    $this->actingAs(paymentUser($order->outlet))
        ->post(route('orders.payments.qris', $order))
        ->assertRedirect();

    $newPayment = Payment::query()->where('order_id', $order->id)->where('id', '!=', $oldPayment->id)->firstOrFail();

    expect($oldPayment->refresh()->status)->toBe('cancelled')
        ->and($newPayment->status)->toBe('pending')
        ->and($newPayment->provider_transaction_id)->toBe('trx-123')
        ->and($newPayment->qris_url)->toBe('https://midtrans.test/qris.png')
        ->and(now()->diffInMinutes($newPayment->expired_at))->toBeGreaterThan(40)
        ->and($order->refresh()->payment_status)->toBe('pending');
});

test('valid midtrans webhook marks active qris payment paid', function () {
    $serverKey = 'server-key';
    BusinessSetting::query()->create([
        'business_name' => 'Bersih Laundry',
        'midtrans_server_key' => $serverKey,
    ]);
    $order = paymentOrder(['payment_status' => 'pending']);
    $payment = Payment::query()->create([
        'outlet_id' => $order->outlet_id,
        'order_id' => $order->id,
        'provider' => 'midtrans',
        'method' => 'qris',
        'status' => 'pending',
        'is_active' => true,
        'amount' => 25000,
        'provider_order_id' => 'MID-001',
        'provider_transaction_id' => 'trx-001',
    ]);
    $order->forceFill(['active_payment_id' => $payment->id])->save();

    $grossAmount = '25000.00';

    $this->postJson(route('webhooks.midtrans'), [
        'order_id' => 'MID-001',
        'transaction_id' => 'trx-001',
        'transaction_status' => 'settlement',
        'status_code' => '200',
        'gross_amount' => $grossAmount,
        'payment_type' => 'qris',
        'signature_key' => midtransSignature('MID-001', '200', $grossAmount, $serverKey),
    ])->assertOk()
        ->assertJsonPath('status', 'processed');

    expect($payment->refresh()->status)->toBe('paid')
        ->and($order->refresh()->payment_status)->toBe('paid')
        ->and($order->order_status)->toBe('received');
});

test('invalid midtrans signature is logged without changing payment', function () {
    BusinessSetting::query()->create([
        'business_name' => 'Bersih Laundry',
        'midtrans_server_key' => 'server-key',
    ]);
    $order = paymentOrder(['payment_status' => 'pending']);
    $payment = Payment::query()->create([
        'outlet_id' => $order->outlet_id,
        'order_id' => $order->id,
        'provider' => 'midtrans',
        'method' => 'qris',
        'status' => 'pending',
        'is_active' => true,
        'amount' => 25000,
        'provider_order_id' => 'MID-002',
    ]);
    $order->forceFill(['active_payment_id' => $payment->id])->save();

    $this->postJson(route('webhooks.midtrans'), [
        'order_id' => 'MID-002',
        'transaction_status' => 'settlement',
        'status_code' => '200',
        'gross_amount' => '25000.00',
        'signature_key' => 'bad-signature',
    ])->assertOk()
        ->assertJsonPath('status', 'failed');

    expect($payment->refresh()->status)->toBe('pending')
        ->and($order->refresh()->payment_status)->toBe('pending');
});

test('late qris webhook after cash payment is marked conflict', function () {
    $serverKey = 'server-key';
    BusinessSetting::query()->create([
        'business_name' => 'Bersih Laundry',
        'midtrans_server_key' => $serverKey,
    ]);
    $order = paymentOrder(['payment_status' => 'paid']);
    $oldQris = Payment::query()->create([
        'outlet_id' => $order->outlet_id,
        'order_id' => $order->id,
        'provider' => 'midtrans',
        'method' => 'qris',
        'status' => 'cancelled',
        'is_active' => false,
        'amount' => 25000,
        'provider_order_id' => 'MID-003',
    ]);
    $cash = Payment::query()->create([
        'outlet_id' => $order->outlet_id,
        'order_id' => $order->id,
        'provider' => 'manual',
        'method' => 'cash',
        'status' => 'paid',
        'is_active' => true,
        'amount' => 25000,
    ]);
    $order->forceFill(['active_payment_id' => $cash->id])->save();
    $grossAmount = '25000.00';

    $this->postJson(route('webhooks.midtrans'), [
        'order_id' => 'MID-003',
        'transaction_status' => 'settlement',
        'status_code' => '200',
        'gross_amount' => $grossAmount,
        'signature_key' => midtransSignature('MID-003', '200', $grossAmount, $serverKey),
    ])->assertOk()
        ->assertJsonPath('status', 'conflict');

    expect($oldQris->refresh()->status)->toBe('cancelled')
        ->and($order->refresh()->active_payment_id)->toBe($cash->id);
});

test('expiry command expires active pending qris payment', function () {
    $order = paymentOrder(['payment_status' => 'pending']);
    $payment = Payment::query()->create([
        'outlet_id' => $order->outlet_id,
        'order_id' => $order->id,
        'provider' => 'midtrans',
        'method' => 'qris',
        'status' => 'pending',
        'is_active' => true,
        'amount' => 25000,
        'expired_at' => now()->subMinute(),
    ]);
    $order->forceFill(['active_payment_id' => $payment->id])->save();

    $this->artisan('payments:expire-qris')
        ->expectsOutput('Expired 1 pending QRIS payment(s).')
        ->assertSuccessful();

    expect($payment->refresh()->status)->toBe('expired')
        ->and($payment->is_active)->toBeFalse()
        ->and($order->refresh()->payment_status)->toBe('expired')
        ->and($order->active_payment_id)->toBeNull();
});
