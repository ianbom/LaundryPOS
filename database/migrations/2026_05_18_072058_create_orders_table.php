<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invoice_number', 100);
            $table->string('order_status', 100)->default('waiting_payment');
            $table->string('payment_status', 100)->default('unpaid');
            $table->foreignId('active_payment_id')->nullable();
            $table->timestamp('order_date')->nullable();
            $table->timestamp('estimated_done_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('additional_fee', 14, 2)->default(0);
            $table->decimal('delivery_fee', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('tracking_token', 150)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['outlet_id', 'invoice_number']);
            $table->index(['outlet_id', 'order_status']);
            $table->index(['outlet_id', 'payment_status']);
            $table->index('tracking_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
