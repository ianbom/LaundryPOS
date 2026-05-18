<?php

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserOutlet;

function phase7Owner(array $attributes = []): User
{
    return User::factory()->create([
        'global_role' => 'owner',
        'is_active' => true,
        ...$attributes,
    ]);
}

function phase7Staff(Outlet $outlet, array $assignment = [], array $attributes = []): User
{
    $user = User::factory()->create([
        'global_role' => 'staff',
        'is_active' => true,
        ...$attributes,
    ]);

    UserOutlet::query()->create([
        'user_id' => $user->id,
        'outlet_id' => $outlet->id,
        'role' => 'staff',
        'can_manage_orders' => true,
        'can_manage_reports' => false,
        'is_primary' => true,
        'is_active' => true,
        ...$assignment,
    ]);

    return $user;
}

function phase7Outlet(string $slug): Outlet
{
    return Outlet::query()->create([
        'name' => str($slug)->headline()->toString(),
        'slug' => $slug,
        'code' => str($slug)->upper()->replace('-', '')->substr(0, 12)->toString(),
        'is_active' => true,
    ]);
}

function phase7Order(Outlet $outlet, array $attributes = []): Order
{
    $customer = Customer::query()->create([
        'outlet_id' => $outlet->id,
        'name' => $attributes['customer_name'] ?? 'Budi Santoso',
        'phone' => $attributes['customer_phone'] ?? '08123456789',
        'whatsapp_number' => $attributes['customer_whatsapp'] ?? '08123456789',
    ]);

    return Order::query()->create([
        'outlet_id' => $outlet->id,
        'customer_id' => $customer->id,
        'invoice_number' => $attributes['invoice_number'] ?? 'INV-'.$outlet->id.'-'.str()->random(8),
        'order_status' => $attributes['order_status'] ?? 'processing',
        'payment_status' => $attributes['payment_status'] ?? 'paid',
        'order_date' => $attributes['order_date'] ?? now(),
        'grand_total' => $attributes['grand_total'] ?? 50000,
        'subtotal' => $attributes['subtotal'] ?? 50000,
        'tracking_token' => $attributes['tracking_token'] ?? 'trk_'.str()->random(20),
    ]);
}

test('staff can update accessible order status and creates timeline and activity log', function () {
    $outlet = phase7Outlet('central');
    $staff = phase7Staff($outlet);
    $order = phase7Order($outlet, ['order_status' => 'processing']);

    $this->actingAs($staff)
        ->patch(route('orders.status.update', $order), [
            'status' => 'ready_to_pickup',
            'notes' => 'Laundry siap diambil.',
        ])
        ->assertRedirect();

    $order->refresh();

    expect($order->order_status)->toBe('ready_to_pickup')
        ->and($order->statusHistories()->count())->toBe(1)
        ->and($order->statusHistories()->first()->old_status)->toBe('processing')
        ->and($order->statusHistories()->first()->new_status)->toBe('ready_to_pickup')
        ->and(ActivityLog::query()->where('action', 'order_status_updated')->exists())->toBeTrue();
});

test('staff cannot update order outside assigned outlet', function () {
    $allowedOutlet = phase7Outlet('allowed');
    $deniedOutlet = phase7Outlet('denied');
    $staff = phase7Staff($allowedOutlet);
    $order = phase7Order($deniedOutlet, ['order_status' => 'processing']);

    $this->actingAs($staff)
        ->patch(route('orders.status.update', $order), [
            'status' => 'ready_to_pickup',
        ])
        ->assertForbidden();
});

test('dashboard metrics respect outlet access and count only paid revenue', function () {
    $allowedOutlet = phase7Outlet('dashboard-allowed');
    $deniedOutlet = phase7Outlet('dashboard-denied');
    $staff = phase7Staff($allowedOutlet);

    phase7Order($allowedOutlet, ['payment_status' => 'paid', 'grand_total' => 80000]);
    phase7Order($allowedOutlet, ['payment_status' => 'unpaid', 'grand_total' => 40000]);
    phase7Order($deniedOutlet, ['payment_status' => 'paid', 'grand_total' => 90000]);

    $this->actingAs($staff)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('metrics.revenueToday', 80000)
            ->where('metrics.ordersToday', 2)
        );
});

test('transaction report respects outlet access and can export csv', function () {
    $allowedOutlet = phase7Outlet('report-allowed');
    $deniedOutlet = phase7Outlet('report-denied');
    $staff = phase7Staff($allowedOutlet, ['can_manage_reports' => true]);

    $allowedOrder = phase7Order($allowedOutlet, ['invoice_number' => 'INV-ALLOWED']);
    phase7Order($deniedOutlet, ['invoice_number' => 'INV-DENIED']);
    Payment::query()->create([
        'outlet_id' => $allowedOutlet->id,
        'order_id' => $allowedOrder->id,
        'provider' => 'manual',
        'method' => 'cash',
        'status' => 'paid',
        'amount' => $allowedOrder->grand_total,
        'paid_at' => now(),
    ]);

    $this->actingAs($staff)
        ->get(route('reports.transactions', ['export' => 'csv']))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8')
        ->assertSee('INV-ALLOWED')
        ->assertDontSee('INV-DENIED');
});

test('owner can filter activity logs', function () {
    $owner = phase7Owner();
    $outlet = phase7Outlet('logs');

    ActivityLog::query()->create([
        'outlet_id' => $outlet->id,
        'user_id' => $owner->id,
        'action' => 'order_status_updated',
        'subject_type' => Order::class,
        'subject_id' => 123,
        'created_at' => now(),
    ]);
    ActivityLog::query()->create([
        'outlet_id' => $outlet->id,
        'user_id' => $owner->id,
        'action' => 'customer_created',
        'created_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('activity-logs.index', ['action' => 'order_status_updated']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('activity-logs/index')
            ->has('logs.data', 1)
            ->where('logs.data.0.action', 'order_status_updated')
        );
});

test('service report aggregates paid order items only', function () {
    $outlet = phase7Outlet('services-report');
    $staff = phase7Staff($outlet, ['can_manage_reports' => true]);
    $paidOrder = phase7Order($outlet, ['payment_status' => 'paid']);
    $unpaidOrder = phase7Order($outlet, ['payment_status' => 'unpaid']);

    OrderItem::query()->create([
        'order_id' => $paidOrder->id,
        'service_name' => 'Cuci Setrika',
        'variant_name' => 'Regular',
        'pricing_type' => 'per_kg',
        'unit' => 'kg',
        'quantity' => 2,
        'charged_quantity' => 3,
        'unit_price' => 10000,
        'subtotal' => 30000,
    ]);
    OrderItem::query()->create([
        'order_id' => $unpaidOrder->id,
        'service_name' => 'Cuci Setrika',
        'variant_name' => 'Regular',
        'pricing_type' => 'per_kg',
        'unit' => 'kg',
        'quantity' => 5,
        'charged_quantity' => 5,
        'unit_price' => 10000,
        'subtotal' => 50000,
    ]);

    $this->actingAs($staff)
        ->get(route('reports.services'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('reports/services')
            ->where('rows.0.service_name', 'Cuci Setrika')
            ->where('rows.0.total_revenue', 30000)
        );
});
