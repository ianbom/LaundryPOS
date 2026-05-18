<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Outlet;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceVariant;
use App\Models\UserOutlet;
use App\Services\OrderPricingService;

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
            'customer_notes' => 'Ambil sore',
            'internal_notes' => 'Cek noda',
        ])
        ->assertRedirect();

    $order = Order::query()->with(['customer', 'items'])->firstOrFail();

    expect($order->customer->whatsapp_number)->toBe('08123456789')
        ->and($order->invoice_number)->toStartWith('LDR-SBY-'.now()->format('Ymd').'-')
        ->and($order->tracking_token)->toStartWith('trk_')
        ->and($order->payment_status)->toBe('unpaid')
        ->and($order->subtotal)->toBe('24000.00')
        ->and($order->grand_total)->toBe('28000.00')
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->service_name)->toBe('Cuci Kering Setrika')
        ->and($order->items->first()->variant_name)->toBe('3x24 Jam')
        ->and($order->items->first()->charged_quantity)->toBe('3.00')
        ->and($order->items->first()->unit_price)->toBe('8000.00')
        ->and(Customer::query()->where('phone', '08123456789')->exists())->toBeTrue()
        ->and(OrderStatusHistory::query()->where('order_id', $order->id)->where('new_status', 'waiting_payment')->exists())->toBeTrue();
});
