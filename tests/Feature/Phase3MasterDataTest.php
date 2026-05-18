<?php

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceVariant;
use App\Models\WhatsappTemplate;

test('owner can create customer for an outlet', function () {
    $outlet = Outlet::query()->create(['name' => 'Pusat', 'slug' => 'pusat']);

    $this->actingAs(ownerUser())
        ->post('/customers', [
            'outlet_id' => $outlet->id,
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'whatsapp_number' => '08123456789',
        ])
        ->assertRedirect('/customers');

    expect(Customer::query()->where('phone', '08123456789')->exists())->toBeTrue();
});

test('staff cannot move customer to inaccessible outlet', function () {
    $source = Outlet::query()->create(['name' => 'Pusat', 'slug' => 'pusat']);
    $target = Outlet::query()->create(['name' => 'Cabang', 'slug' => 'cabang']);
    $customer = Customer::query()->create([
        'outlet_id' => $source->id,
        'name' => 'Budi Santoso',
        'phone' => '08123456789',
    ]);

    $this->actingAs(staffUserWithServiceAccess($source))
        ->put("/customers/{$customer->id}", [
            'outlet_id' => $target->id,
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
        ])
        ->assertForbidden();

    expect($customer->fresh()->outlet_id)->toBe($source->id);
});

test('service cannot use category from another outlet', function () {
    $source = Outlet::query()->create(['name' => 'Pusat', 'slug' => 'pusat']);
    $target = Outlet::query()->create(['name' => 'Cabang', 'slug' => 'cabang']);
    $category = ServiceCategory::query()->create([
        'outlet_id' => $source->id,
        'name' => 'Laundry Kiloan',
    ]);

    $this->actingAs(staffUserWithServiceAccess($target))
        ->post('/services', [
            'outlet_id' => $target->id,
            'service_category_id' => $category->id,
            'name' => 'Cuci Kering',
            'pricing_type' => 'per_kg',
        ])
        ->assertSessionHasErrors('service_category_id');
});

test('service variant inherits service outlet', function () {
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

    $this->actingAs(staffUserWithServiceAccess($outlet))
        ->post("/services/{$service->id}/variants", [
            'name' => '4x24 Jam',
            'price' => 7000,
            'unit' => 'kg',
            'min_quantity' => 3,
        ])
        ->assertRedirect("/services/{$service->id}/variants");

    expect(ServiceVariant::query()->where('service_id', $service->id)->value('outlet_id'))
        ->toBe($outlet->id);
});

test('whatsapp template preview keeps unknown variables unchanged', function () {
    $template = WhatsappTemplate::query()->create([
        'type' => 'payment_receipt',
        'title' => 'Receipt',
        'body' => 'Halo {customer_name}, invoice {invoice_number}, {unknown_value}',
    ]);

    $this->actingAs(ownerUser())
        ->post("/settings/whatsapp-templates/{$template->id}/preview")
        ->assertOk()
        ->assertJsonPath(
            'message',
            'Halo Budi Santoso, invoice LDR-20260518-0001, {unknown_value}',
        );
});
