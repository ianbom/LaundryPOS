<?php

use App\Models\Outlet;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceVariant;

test('owner can copy service catalog between outlets', function () {
    $source = Outlet::query()->create(['name' => 'Pusat', 'slug' => 'pusat']);
    $target = Outlet::query()->create(['name' => 'Cabang', 'slug' => 'cabang']);
    $category = ServiceCategory::query()->create([
        'outlet_id' => $source->id,
        'name' => 'Laundry Kiloan',
        'description' => 'Kiloan',
    ]);
    $service = Service::query()->create([
        'outlet_id' => $source->id,
        'service_category_id' => $category->id,
        'name' => 'Cuci Kering Setrika',
        'pricing_type' => 'per_kg',
    ]);
    ServiceVariant::query()->create([
        'outlet_id' => $source->id,
        'service_id' => $service->id,
        'name' => '4x24 Jam',
        'price' => 7000,
        'unit' => 'kg',
        'min_quantity' => 3,
    ]);

    $this->actingAs(ownerUser())
        ->post('/services/copy', [
            'source_outlet_id' => $source->id,
            'target_outlet_id' => $target->id,
            'copy_mode' => 'skip_existing',
            'include_inactive' => false,
        ])
        ->assertRedirect('/services/copy');

    $targetCategory = ServiceCategory::query()
        ->where('outlet_id', $target->id)
        ->where('name', 'Laundry Kiloan')
        ->firstOrFail();

    $targetService = Service::query()
        ->where('outlet_id', $target->id)
        ->where('service_category_id', $targetCategory->id)
        ->where('name', 'Cuci Kering Setrika')
        ->firstOrFail();

    expect($targetService->variants()->where('name', '4x24 Jam')->exists())->toBeTrue();
});

test('copy service catalog rejects same outlet target', function () {
    $outlet = Outlet::query()->create(['name' => 'Pusat', 'slug' => 'pusat']);

    $this->actingAs(ownerUser())
        ->post('/services/copy', [
            'source_outlet_id' => $outlet->id,
            'target_outlet_id' => $outlet->id,
            'copy_mode' => 'skip_existing',
            'include_inactive' => false,
        ])
        ->assertSessionHasErrors('target_outlet_id');
});
