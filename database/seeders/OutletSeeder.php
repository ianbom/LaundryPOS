<?php

namespace Database\Seeders;

use App\Models\Outlet;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            [
                'name' => 'Central Surabaya',
                'code' => 'SBY',
                'slug' => 'central-surabaya',
                'phone' => '031-847-1928',
                'whatsapp_number' => '6281284719203',
                'email' => 'central@bersihlaundry.test',
                'address' => 'Jl. Raya Darmo No. 42, Surabaya',
                'google_maps_url' => 'https://maps.google.com/?q=Central+Surabaya+Bersih+Laundry',
                'is_main' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Sidoarjo Kota',
                'code' => 'SDA',
                'slug' => 'sidoarjo-kota',
                'phone' => '031-892-4471',
                'whatsapp_number' => '6281289244710',
                'email' => 'sidoarjo@bersihlaundry.test',
                'address' => 'Jl. Pahlawan No. 18, Sidoarjo',
                'google_maps_url' => 'https://maps.google.com/?q=Sidoarjo+Bersih+Laundry',
                'is_main' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Gresik Barat',
                'code' => 'GSK',
                'slug' => 'gresik-barat',
                'phone' => '031-399-6184',
                'whatsapp_number' => '6281239961840',
                'email' => 'gresik@bersihlaundry.test',
                'address' => 'Jl. Veteran No. 11, Gresik',
                'google_maps_url' => 'https://maps.google.com/?q=Gresik+Bersih+Laundry',
                'is_main' => false,
                'is_active' => true,
            ],
        ])->each(fn (array $outlet) => Outlet::updateOrCreate(
            ['slug' => $outlet['slug']],
            $outlet,
        ));
    }
}
