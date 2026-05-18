<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Outlet;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $outlets = Outlet::query()->get()->keyBy('code');

        collect([
            ['SBY', 'Budi Santoso', '0812-3456-7890', '6281234567890', 'Jl. Manyar Kertoarjo No. 8, Surabaya'],
            ['SBY', 'Siti Nurhaliza', '0813-2345-6789', '6281323456789', 'Jl. Ngagel Jaya Selatan No. 17, Surabaya'],
            ['SBY', 'Andi Pratama', '0812-9876-5432', '6281298765432', 'Jl. Bratang Binangun No. 22, Surabaya'],
            ['SBY', 'Dewi Lestari', '0813-1111-2222', '6281311112222', 'Jl. Dharmahusada Indah No. 5, Surabaya'],
            ['SBY', 'Hendra Wijaya', '0812-2222-3333', '6281222223333', 'Jl. Kertajaya No. 64, Surabaya'],
            ['SBY', 'Rina Kartika', '0813-4444-5555', '6281344445555', 'Apartemen Puncak Marina Tower B'],
            ['SDA', 'Lukman Hakim', '0821-2190-4817', '6282121904817', 'Jl. Diponegoro No. 31, Sidoarjo'],
            ['SDA', 'Ayu Maharani', '0857-3184-6209', '6285731846209', 'Perumahan Taman Pinang Indah Blok C4'],
            ['GSK', 'Bagas Saputra', '0822-6319-8044', '6282263198044', 'Jl. Kartini No. 9, Gresik'],
        ])->each(function (array $customer) use ($outlets): void {
            [$outletCode, $name, $phone, $whatsapp, $address] = $customer;

            Customer::updateOrCreate(
                ['phone' => $phone],
                [
                    'outlet_id' => $outlets[$outletCode]->id,
                    'name' => $name,
                    'whatsapp_number' => $whatsapp,
                    'address' => $address,
                    'notes' => 'Pelanggan seed dari PRD laundry POS.',
                ],
            );
        });
    }
}
