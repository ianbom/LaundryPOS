<?php

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\PosPaymentIntent;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceVariant;
use App\Models\UserOutlet;
use App\Models\WhatsappMessage;
use App\Services\OrderPricingService;
use Illuminate\Support\Facades\Http;

test('order pricing uses minimum quantity and clamps grand total', function () {
    $outlet = Outlet::query()->create(['name' => 'Pusat', 'slug' => 'pusat']);
    $category = ServiceCategory::query()->create([
        'outlet_id' => $outlet->id,
        'name' => 'Laundry Kiloan',
    ]);
    $service = Service::query()->create([
        'outlet_id' => $outlet->id,
        'service_category_id' => $category->id,
        'name' => 'Cuci Kering Setrika',
        'pricing_type' => 'per_kg',
    ]);
    $variant = ServiceVariant::query()->create([
        'outlet_id' => $outlet->id,
        'service_id' => $service->id,
        'name' => '3x24 Jam',
        'price' => 8000,
        'unit' => 'kg',
        'min_quantity' => 3,
    ]);

    $pricing = app(OrderPricingService::class);

    expect($pricing->calculateItem($variant, 2))
        ->toMatchArray([
            'quantity' => 2.0,
            'charged_quantity' => 3.0,
            'unit_price' => 8000.0,
            'subtotal' => 24000.0,
        ])
        ->and($pricing->calculateOrderTotals([
            ['subtotal' => 24000],
        ], 30000, 1000, 0))
        ->toMatchArray([
            'subtotal' => 24000.0,
            'discount_amount' => 30000.0,
            'additional_fee' => 1000.0,
            'delivery_fee' => 0.0,
            'grand_total' => 0.0,
        ]);
});

test('cashier can generate POS QRIS and order is created after payment webhook', function () {
    Http::fake([
        'api.sandbox.midtrans.com/v2/charge' => Http::response([
            'transaction_id' => 'trx-pos-001',
            'reference_id' => 'ref-pos-001',
            'actions' => [
                ['name' => 'generate-qr-code', 'url' => 'https://midtrans.test/pos-qris.png'],
                ['name' => 'deeplink-redirect', 'url' => 'https://midtrans.test/pay-pos'],
            ],
        ]),
    ]);

    $serverKey = 'server-key';
    config(['services.midtrans.server_key' => $serverKey]);
    BusinessSetting::query()->create([
        'business_name' => 'Bersih Laundry',
        'midtrans_server_key' => $serverKey,
        'qris_expiry_minutes' => 30,
    ]);

    $outlet = Outlet::query()->create([
        'name' => 'Central Surabaya',
        'code' => 'SBY',
        'slug' => 'central-surabaya',
    ]);
    $user = ownerUser();
    $category = ServiceCategory::query()->create([
        'outlet_id' => $outlet->id,
        'name' => 'Laundry Kiloan',
    ]);
    $service = Service::query()->create([
        'outlet_id' => $outlet->id,
        'service_category_id' => $category->id,
        'name' => 'Cuci Kering Setrika',
        'pricing_type' => 'per_kg',
    ]);
    $variant = ServiceVariant::query()->create([
        'outlet_id' => $outlet->id,
        'service_id' => $service->id,
        'name' => '3x24 Jam',
        'price' => 8000,
        'unit' => 'kg',
        'min_quantity' => 3,
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('pos.orders.qris'), [
            'outlet_id' => $outlet->id,
            'customer' => [
                'name' => 'Siti Aminah',
                'phone' => '081111111111',
                'whatsapp_number' => '081111111111',
            ],
            'items' => [
                [
                    'service_variant_id' => $variant->id,
                    'quantity' => 4,
                ],
            ],
            'payment_method' => 'qris',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('qris_url', 'https://midtrans.test/pos-qris.png');

    expect($response->json('provider_order_id'))->toStartWith('POS-'.$outlet->id.'-')
        ->and(Order::query()->count())->toBe(0)
        ->and(Payment::query()->count())->toBe(0)
        ->and(PosPaymentIntent::query()->count())->toBe(1);

    $intent = PosPaymentIntent::query()->firstOrFail();
    $grossAmount = '32000.00';

    $this->postJson(route('webhooks.midtrans'), [
        'order_id' => $intent->provider_order_id,
        'transaction_id' => 'trx-pos-001',
        'transaction_status' => 'settlement',
        'status_code' => '200',
        'gross_amount' => $grossAmount,
        'payment_type' => 'qris',
        'signature_key' => hash('sha512', $intent->provider_order_id.'200'.$grossAmount.$serverKey),
    ])
        ->assertOk()
        ->assertJsonPath('status', 'processed');

    $order = Order::query()->firstOrFail();
    $payment = Payment::query()->whereBelongsTo($order)->firstOrFail();

    expect($intent->refresh()->status)->toBe('paid')
        ->and((int) $intent->order_id)->toBe($order->id)
        ->and((int) $intent->payment_id)->toBe($payment->id)
        ->and($order->payment_status)->toBe('paid')
        ->and($order->order_status)->toBe('received')
        ->and((int) $order->active_payment_id)->toBe($payment->id)
        ->and($payment->method)->toBe('qris')
        ->and($payment->provider)->toBe('midtrans')
        ->and($payment->status)->toBe('paid')
        ->and($payment->provider_transaction_id)->toBe('trx-pos-001')
        ->and($payment->qris_url)->toBe('https://midtrans.test/pos-qris.png')
        ->and(WhatsappMessage::query()->where('order_id', $order->id)->where('message_type', 'payment_receipt')->exists())->toBeTrue();
});

test('cashier can create POS order with quick customer and item snapshots', function () {
    $outlet = Outlet::query()->create([
        'name' => 'Central Surabaya',
        'code' => 'SBY',
        'slug' => 'central-surabaya',
    ]);
    $user = ownerUser();
    UserOutlet::query()->create([
        'user_id' => $user->id,
        'outlet_id' => $outlet->id,
        'role' => 'cashier',
        'can_manage_orders' => true,
        'is_primary' => true,
        'is_active' => true,
    ]);
    $category = ServiceCategory::query()->create([
        'outlet_id' => $outlet->id,
        'name' => 'Laundry Kiloan',
    ]);
    $service = Service::query()->create([
        'outlet_id' => $outlet->id,
        'service_category_id' => $category->id,
        'name' => 'Cuci Kering Setrika',
        'pricing_type' => 'per_kg',
    ]);
    $variant = ServiceVariant::query()->create([
        'outlet_id' => $outlet->id,
        'service_id' => $service->id,
        'name' => '3x24 Jam',
        'price' => 8000,
        'unit' => 'kg',
        'min_quantity' => 3,
        'estimated_duration_hours' => 72,
    ]);

    $this->actingAs($user)
        ->post('/pos/orders', [
            'outlet_id' => $outlet->id,
            'customer' => [
                'name' => 'Budi Santoso',
                'phone' => '08123456789',
                'whatsapp_number' => '',
                'address' => 'Jl. Mawar',
                'notes' => 'Pelanggan baru',
            ],
            'items' => [
                [
                    'service_variant_id' => $variant->id,
                    'quantity' => 2,
                    'notes' => 'Pisahkan putih',
                ],
            ],
            'discount_amount' => 1000,
            'additional_fee' => 2000,
            'delivery_fee' => 3000,
            'payment_method' => 'cash',
            'amount_paid' => 30000,
            'customer_notes' => 'Ambil sore',
            'internal_notes' => 'Cek noda',
        ])
        ->assertRedirect();

    $order = Order::query()->with(['customer', 'items'])->firstOrFail();

    expect($order->customer->whatsapp_number)->toBe('08123456789')
        ->and($order->invoice_number)->toStartWith('LDR-SBY-'.now()->format('Ymd').'-')
        ->and($order->tracking_token)->toStartWith('trk_')
        ->and($order->payment_status)->toBe('paid')
        ->and($order->order_status)->toBe('received')
        ->and($order->subtotal)->toBe('24000.00')
        ->and($order->grand_total)->toBe('28000.00')
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->service_name)->toBe('Cuci Kering Setrika')
        ->and($order->items->first()->variant_name)->toBe('3x24 Jam')
        ->and($order->items->first()->charged_quantity)->toBe('3.00')
        ->and($order->items->first()->unit_price)->toBe('8000.00')
        ->and(Customer::query()->where('phone', '08123456789')->exists())->toBeTrue()
        ->and(OrderStatusHistory::query()->where('order_id', $order->id)->where('new_status', 'waiting_payment')->exists())->toBeTrue()
        ->and(OrderStatusHistory::query()->where('order_id', $order->id)->where('new_status', 'received')->exists())->toBeTrue()
        ->and(Payment::query()->where('order_id', $order->id)->where('method', 'cash')->where('status', 'paid')->exists())->toBeTrue()
        ->and(WhatsappMessage::query()->where('order_id', $order->id)->where('message_type', 'payment_receipt')->exists())->toBeTrue();
});
