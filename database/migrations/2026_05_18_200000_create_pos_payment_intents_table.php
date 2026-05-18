<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_payment_intents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 50)->default('pending');
            $table->decimal('amount', 14, 2)->default(0);
            $table->json('order_payload');
            $table->json('order_snapshots');
            $table->json('order_totals');
            $table->string('provider_order_id', 150)->unique();
            $table->string('provider_transaction_id', 150)->nullable();
            $table->string('provider_reference_id', 150)->nullable();
            $table->text('qris_string')->nullable();
            $table->text('qris_url')->nullable();
            $table->text('payment_url')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'status']);
            $table->index('provider_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_payment_intents');
    }
};
