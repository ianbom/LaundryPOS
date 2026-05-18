<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 50);
            $table->string('method', 50);
            $table->string('status', 50)->default('pending');
            $table->boolean('is_active')->default(true);
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('amount_paid', 14, 2)->nullable();
            $table->decimal('change_amount', 14, 2)->nullable();
            $table->string('provider_order_id', 150)->nullable();
            $table->string('provider_transaction_id', 150)->nullable();
            $table->string('provider_reference_id', 150)->nullable();
            $table->text('qris_string')->nullable();
            $table->text('qris_url')->nullable();
            $table->text('payment_url')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index('outlet_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('provider_order_id');
            $table->index('provider_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
