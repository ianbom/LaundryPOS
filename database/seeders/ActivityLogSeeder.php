<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@bersihlaundry.test')->first();

        Order::query()->with('outlet')->get()->each(function (Order $order, int $index) use ($admin): void {
            ActivityLog::updateOrCreate(
                [
                    'subject_type' => Order::class,
                    'subject_id' => $order->id,
                    'action' => 'order.seeded',
                ],
                [
                    'outlet_id' => $order->outlet_id,
                    'user_id' => $admin?->id,
                    'description' => 'Demo order '.$order->invoice_number.' dibuat dari seeder PRD.',
                    'old_values' => null,
                    'new_values' => [
                        'invoice_number' => $order->invoice_number,
                        'order_status' => $order->order_status,
                        'payment_status' => $order->payment_status,
                        'grand_total' => $order->grand_total,
                    ],
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Laravel Seeder',
                    'created_at' => Carbon::parse('2026-05-18 10:30:00')->addMinutes($index),
                ],
            );
        });
    }
}
