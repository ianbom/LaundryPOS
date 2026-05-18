<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\PaymentWebhook;
use App\Models\ServiceVariant;
use App\Models\User;
use App\Models\WhatsappMessage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OrderDemoSeeder extends Seeder
{
    public function run(): void
    {
        $outlet = Outlet::query()->where('code', 'SBY')->firstOrFail();
        $cashier = User::query()->where('email', 'cashier.sby@bersihlaundry.test')->firstOrFail();

        collect([
            [
                'invoice' => 'SBY-20260518-0001',
                'customer_phone' => '0812-3456-7890',
                'service' => 'Cuci Kering Setrika',
                'variant' => '2x24 Jam',
                'quantity' => 5,
                'status' => 'ready_to_pickup',
                'payment_status' => 'paid',
                'method' => 'qris',
                'order_date' => '2026-05-18 09:30:00',
            ],
            [
                'invoice' => 'SBY-20260518-0002',
                'customer_phone' => '0813-2345-6789',
                'service' => 'Cuci Kering',
                'variant' => '2x24 Jam',
                'quantity' => 8,
                'status' => 'processing',
                'payment_status' => 'pending',
                'method' => 'qris',
                'order_date' => '2026-05-18 09:15:00',
            ],
            [
                'invoice' => 'SBY-20260518-0003',
                'customer_phone' => '0812-9876-5432',
                'service' => 'Cuci Kering Setrika',
                'variant' => 'Express <24 Jam',
                'quantity' => 10,
                'status' => 'washing',
                'payment_status' => 'paid',
                'method' => 'cash',
                'order_date' => '2026-05-18 08:45:00',
            ],
            [
                'invoice' => 'SBY-20260518-0004',
                'customer_phone' => '0813-1111-2222',
                'service' => 'Cuci Kering',
                'variant' => '4x24 Jam',
                'quantity' => 6,
                'status' => 'cancelled',
                'payment_status' => 'failed',
                'method' => 'qris',
                'order_date' => '2026-05-18 08:20:00',
            ],
            [
                'invoice' => 'SBY-20260518-0005',
                'customer_phone' => '0812-2222-3333',
                'service' => 'Cuci Bed Cover',
                'variant' => 'Queen',
                'quantity' => 1,
                'status' => 'completed',
                'payment_status' => 'paid',
                'method' => 'cash',
                'order_date' => '2026-05-18 07:50:00',
            ],
            [
                'invoice' => 'SBY-20260518-0006',
                'customer_phone' => '0813-4444-5555',
                'service' => 'Cuci Kering Setrika',
                'variant' => '4x24 Jam',
                'quantity' => 4,
                'status' => 'ironing',
                'payment_status' => 'pending',
                'method' => 'qris',
                'order_date' => '2026-05-18 07:30:00',
            ],
        ])->each(fn (array $data) => $this->seedOrder($data, $outlet, $cashier));

        $this->refreshCustomerStats();
    }

    private function seedOrder(array $data, Outlet $outlet, User $cashier): void
    {
        $customer = Customer::query()->where('phone', $data['customer_phone'])->firstOrFail();
        $variant = ServiceVariant::query()
            ->where('outlet_id', $outlet->id)
            ->where('name', $data['variant'])
            ->whereHas('service', fn ($query) => $query->where('name', $data['service']))
            ->with('service.serviceCategory')
            ->firstOrFail();

        $orderDate = Carbon::parse($data['order_date'], 'Asia/Jakarta');
        $chargedQuantity = max((float) $data['quantity'], (float) $variant->min_quantity);
        $subtotal = $chargedQuantity * (float) $variant->price;
        $additionalFee = $data['method'] === 'qris' ? 1000 : 0;
        $grandTotal = $subtotal + $additionalFee;

        $order = Order::updateOrCreate(
            [
                'outlet_id' => $outlet->id,
                'invoice_number' => $data['invoice'],
            ],
            [
                'customer_id' => $customer->id,
                'created_by' => $cashier->id,
                'order_status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'order_date' => $orderDate,
                'estimated_done_at' => $orderDate->copy()->addHours((int) $variant->estimated_duration_hours),
                'completed_at' => $data['status'] === 'completed' ? $orderDate->copy()->addDays(2) : null,
                'cancelled_at' => $data['status'] === 'cancelled' ? $orderDate->copy()->addMinutes(35) : null,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'additional_fee' => $additionalFee,
                'delivery_fee' => 0,
                'grand_total' => $grandTotal,
                'customer_notes' => 'Tolong gunakan parfum lembut.',
                'internal_notes' => $data['status'] === 'cancelled' ? 'QRIS gagal, order dibatalkan oleh kasir.' : null,
                'tracking_token' => 'track_'.Str::lower(Str::random(32)),
            ],
        );

        OrderItem::updateOrCreate(
            [
                'order_id' => $order->id,
                'service_variant_id' => $variant->id,
            ],
            [
                'service_category_id' => $variant->service->serviceCategory->id,
                'service_id' => $variant->service->id,
                'service_name' => $variant->service->name,
                'variant_name' => $variant->name,
                'pricing_type' => $variant->service->pricing_type,
                'unit' => $variant->unit,
                'quantity' => $data['quantity'],
                'charged_quantity' => $chargedQuantity,
                'unit_price' => $variant->price,
                'subtotal' => $subtotal,
                'notes' => 'Snapshot harga saat transaksi dibuat.',
            ],
        );

        $payment = $this->seedPayment($order, $outlet, $cashier, $data, $grandTotal, $orderDate);
        $order->forceFill(['active_payment_id' => $payment->id])->save();

        $this->seedStatusHistory($order, $cashier, $data['status'], $orderDate);
        $this->seedWhatsappMessages($order, $outlet, $customer, $data, $orderDate);
    }

    private function seedPayment(Order $order, Outlet $outlet, User $cashier, array $data, float $amount, Carbon $orderDate): Payment
    {
        if ($data['method'] === 'cash') {
            return Payment::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'method' => 'cash',
                ],
                [
                    'outlet_id' => $outlet->id,
                    'provider' => 'manual',
                    'status' => 'paid',
                    'is_active' => true,
                    'amount' => $amount,
                    'amount_paid' => $amount + 50000,
                    'change_amount' => 50000,
                    'paid_at' => $orderDate->copy()->addMinutes(2),
                    'confirmed_by' => $cashier->id,
                    'raw_response' => [
                        'source' => 'seed',
                        'confirmation' => 'manual_cash',
                    ],
                ],
            );
        }

        $status = $data['payment_status'] === 'paid'
            ? 'paid'
            : ($data['payment_status'] === 'failed' ? 'failed' : 'pending');

        $payment = Payment::updateOrCreate(
            [
                'order_id' => $order->id,
                'method' => 'qris',
            ],
            [
                'outlet_id' => $outlet->id,
                'provider' => 'midtrans',
                'status' => $status,
                'is_active' => true,
                'amount' => $amount,
                'provider_order_id' => 'MID-'.$order->invoice_number,
                'provider_transaction_id' => 'trx-'.Str::lower(Str::random(18)),
                'provider_reference_id' => 'ref-'.Str::lower(Str::random(18)),
                'qris_string' => '00020101021226690016ID.CO.MIDTRANS.WWW01189360091412345678900215'.$order->invoice_number,
                'qris_url' => 'https://example.test/qris/'.$order->invoice_number.'.png',
                'payment_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.$order->invoice_number,
                'expired_at' => $orderDate->copy()->addMinutes(30),
                'paid_at' => $status === 'paid' ? $orderDate->copy()->addMinutes(8) : null,
                'cancelled_at' => $status === 'failed' ? $orderDate->copy()->addMinutes(30) : null,
                'raw_response' => [
                    'source' => 'seed',
                    'payment_type' => 'qris',
                    'transaction_status' => $status === 'paid' ? 'settlement' : $status,
                ],
            ],
        );

        if ($status !== 'pending') {
            PaymentWebhook::updateOrCreate(
                [
                    'payment_id' => $payment->id,
                    'provider_transaction_id' => $payment->provider_transaction_id,
                ],
                [
                    'order_id' => $order->id,
                    'provider' => 'midtrans',
                    'provider_order_id' => $payment->provider_order_id,
                    'event_type' => 'payment.notification',
                    'transaction_status' => $status === 'paid' ? 'settlement' : 'deny',
                    'fraud_status' => $status === 'paid' ? 'accept' : 'deny',
                    'payment_type' => 'qris',
                    'gross_amount' => $amount,
                    'signature_key' => 'demo-signature-key',
                    'is_valid_signature' => true,
                    'is_processed' => true,
                    'processed_at' => $orderDate->copy()->addMinutes(9),
                    'process_status' => $status === 'paid' ? 'processed' : 'failed',
                    'process_message' => $status === 'paid' ? 'Seed webhook paid.' : 'Seed webhook failed.',
                    'raw_payload' => [
                        'order_id' => $payment->provider_order_id,
                        'transaction_id' => $payment->provider_transaction_id,
                        'gross_amount' => $amount,
                    ],
                    'created_at' => $orderDate->copy()->addMinutes(9),
                ],
            );
        }

        return $payment;
    }

    private function seedStatusHistory(Order $order, User $cashier, string $currentStatus, Carbon $orderDate): void
    {
        $flow = ['waiting_payment', 'received', 'processing', 'washing', 'drying', 'ironing', 'ready_to_pickup', 'completed'];
        $targetIndex = array_search($currentStatus, $flow, true);

        if ($currentStatus === 'cancelled') {
            $flow = ['waiting_payment', 'cancelled'];
            $targetIndex = 1;
        }

        if ($targetIndex === false) {
            $targetIndex = 0;
        }

        $previous = null;
        foreach (array_slice($flow, 0, $targetIndex + 1) as $index => $status) {
            OrderStatusHistory::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'new_status' => $status,
                ],
                [
                    'old_status' => $previous,
                    'changed_by' => $cashier->id,
                    'notes' => 'Status seed: '.$status,
                    'created_at' => $orderDate->copy()->addMinutes($index * 20),
                ],
            );

            $previous = $status;
        }
    }

    private function seedWhatsappMessages(Order $order, Outlet $outlet, Customer $customer, array $data, Carbon $orderDate): void
    {
        if ($data['payment_status'] === 'paid') {
            WhatsappMessage::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'message_type' => 'payment_receipt',
                ],
                [
                    'outlet_id' => $outlet->id,
                    'customer_id' => $customer->id,
                    'provider' => 'fonnte',
                    'phone' => $customer->whatsapp_number ?? $customer->phone,
                    'message_body' => "Halo {$customer->name}, pembayaran laundry kamu berhasil.\n\nInvoice: {$order->invoice_number}\nTotal: Rp ".number_format((float) $order->grand_total, 0, ',', '.')."\n\nTracking: /track/{$order->tracking_token}",
                    'status' => 'sent',
                    'provider_message_id' => 'wa-'.Str::lower(Str::random(16)),
                    'raw_response' => ['source' => 'seed', 'status' => 'sent'],
                    'sent_at' => $orderDate->copy()->addMinutes(10),
                ],
            );
        }

        if (in_array($data['status'], ['ready_to_pickup', 'completed'], true)) {
            WhatsappMessage::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'message_type' => $data['status'] === 'completed' ? 'order_completed' : 'order_ready',
                ],
                [
                    'outlet_id' => $outlet->id,
                    'customer_id' => $customer->id,
                    'provider' => 'fonnte',
                    'phone' => $customer->whatsapp_number ?? $customer->phone,
                    'message_body' => "Halo {$customer->name}, laundry kamu sudah ".($data['status'] === 'completed' ? 'selesai' : 'siap diambil').".\n\nInvoice: {$order->invoice_number}\nTracking: /track/{$order->tracking_token}",
                    'status' => 'sent',
                    'provider_message_id' => 'wa-'.Str::lower(Str::random(16)),
                    'raw_response' => ['source' => 'seed', 'status' => 'sent'],
                    'sent_at' => $orderDate->copy()->addDay(),
                ],
            );
        }
    }

    private function refreshCustomerStats(): void
    {
        Customer::query()->with('orders')->get()->each(function (Customer $customer): void {
            $paidOrders = $customer->orders->where('payment_status', 'paid');

            $customer->forceFill([
                'total_orders' => $customer->orders->count(),
                'total_spent' => $paidOrders->sum('grand_total'),
            ])->save();
        });
    }
}
