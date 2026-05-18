<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceVariant;
use Illuminate\Database\Seeder;

class LaundryServiceSeeder extends Seeder
{
    public function run(): void
    {
        $serviceCatalog = [
            [
                'category' => 'Laundry Kiloan',
                'description' => 'Layanan kiloan reguler untuk pakaian harian.',
                'services' => [
                    [
                        'name' => 'Cuci Kering Setrika',
                        'pricing_type' => 'per_kg',
                        'variants' => [
                            ['4x24 Jam', 7000, 'kg', 3, 96, false],
                            ['2x24 Jam', 9000, 'kg', 3, 48, false],
                            ['Express <24 Jam', 12000, 'kg', 3, 24, true],
                        ],
                    ],
                    [
                        'name' => 'Cuci Kering',
                        'pricing_type' => 'per_kg',
                        'variants' => [
                            ['4x24 Jam', 5500, 'kg', 3, 96, false],
                            ['2x24 Jam', 7500, 'kg', 3, 48, false],
                            ['Express <24 Jam', 10000, 'kg', 3, 24, true],
                        ],
                    ],
                    [
                        'name' => 'Setrika Saja',
                        'pricing_type' => 'per_kg',
                        'variants' => [
                            ['3x24 Jam', 5000, 'kg', 3, 72, false],
                            ['Express <24 Jam', 8500, 'kg', 3, 24, true],
                        ],
                    ],
                ],
            ],
            [
                'category' => 'Laundry Satuan',
                'description' => 'Layanan satuan untuk pakaian khusus.',
                'services' => [
                    [
                        'name' => 'Jas Formal',
                        'pricing_type' => 'per_item',
                        'variants' => [
                            ['Reguler', 35000, 'pcs', 1, 72, false],
                            ['Express', 52000, 'pcs', 1, 24, true],
                        ],
                    ],
                    [
                        'name' => 'Kemeja Premium',
                        'pricing_type' => 'per_item',
                        'variants' => [
                            ['Reguler', 15000, 'pcs', 1, 48, false],
                            ['Express', 24000, 'pcs', 1, 18, true],
                        ],
                    ],
                ],
            ],
            [
                'category' => 'Bedcover',
                'description' => 'Cuci bedcover, selimut, dan perlengkapan tidur.',
                'services' => [
                    [
                        'name' => 'Cuci Bed Cover',
                        'pricing_type' => 'per_item',
                        'variants' => [
                            ['Single', 45000, 'pcs', 1, 96, false],
                            ['Queen', 60000, 'pcs', 1, 96, false],
                            ['King', 75000, 'pcs', 1, 120, false],
                        ],
                    ],
                    [
                        'name' => 'Cuci Selimut',
                        'pricing_type' => 'per_item',
                        'variants' => [
                            ['Reguler', 38000, 'pcs', 1, 72, false],
                        ],
                    ],
                ],
            ],
            [
                'category' => 'Sepatu',
                'description' => 'Cuci sepatu canvas, sneakers, dan premium.',
                'services' => [
                    [
                        'name' => 'Sepatu Premium',
                        'pricing_type' => 'per_item',
                        'variants' => [
                            ['Deep Clean', 65000, 'pasang', 1, 120, false],
                            ['Express Clean', 90000, 'pasang', 1, 48, true],
                        ],
                    ],
                ],
            ],
            [
                'category' => 'Extra Service',
                'description' => 'Tambahan layanan operasional.',
                'services' => [
                    [
                        'name' => 'Antar Jemput',
                        'pricing_type' => 'fixed',
                        'variants' => [
                            ['Area Outlet', 15000, 'trip', 1, 0, false],
                            ['Luar Area', 25000, 'trip', 1, 0, false],
                        ],
                    ],
                    [
                        'name' => 'Parfum Premium',
                        'pricing_type' => 'fixed',
                        'variants' => [
                            ['Ocean Fresh', 5000, 'order', 1, 0, false],
                            ['Soft Floral', 5000, 'order', 1, 0, false],
                        ],
                    ],
                ],
            ],
        ];

        Outlet::query()->where('is_active', true)->get()->each(function (Outlet $outlet) use ($serviceCatalog): void {
            collect($serviceCatalog)->each(function (array $categoryData, int $categoryIndex) use ($outlet): void {
                $category = ServiceCategory::updateOrCreate(
                    [
                        'outlet_id' => $outlet->id,
                        'name' => $categoryData['category'],
                    ],
                    [
                        'description' => $categoryData['description'],
                        'sort_order' => $categoryIndex + 1,
                        'is_active' => true,
                    ],
                );

                collect($categoryData['services'])->each(function (array $serviceData, int $serviceIndex) use ($outlet, $category): void {
                    $service = Service::updateOrCreate(
                        [
                            'outlet_id' => $outlet->id,
                            'service_category_id' => $category->id,
                            'name' => $serviceData['name'],
                        ],
                        [
                            'description' => 'Master layanan '.$serviceData['name'].' untuk '.$outlet->name.'.',
                            'pricing_type' => $serviceData['pricing_type'],
                            'sort_order' => $serviceIndex + 1,
                            'is_active' => true,
                        ],
                    );

                    collect($serviceData['variants'])->each(function (array $variantData, int $variantIndex) use ($outlet, $service): void {
                        [$name, $price, $unit, $minQuantity, $durationHours, $isExpress] = $variantData;

                        ServiceVariant::updateOrCreate(
                            [
                                'outlet_id' => $outlet->id,
                                'service_id' => $service->id,
                                'name' => $name,
                            ],
                            [
                                'description' => 'Varian '.$name.' untuk layanan '.$service->name.'.',
                                'price' => $price,
                                'unit' => $unit,
                                'min_quantity' => $minQuantity,
                                'estimated_duration_hours' => $durationHours,
                                'is_express' => $isExpress,
                                'is_active' => true,
                                'sort_order' => $variantIndex + 1,
                            ],
                        );
                    });
                });
            });
        });
    }
}
