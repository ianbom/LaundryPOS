<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 50);
            $table->string('provider_order_id', 150)->nullable();
            $table->string('provider_transaction_id', 150)->nullable();
            $table->string('event_type', 100)->nullable();
            $table->string('transaction_status', 100)->nullable();
            $table->string('fraud_status', 100)->nullable();
            $table->string('payment_type', 100)->nullable();
            $table->decimal('gross_amount', 14, 2)->nullable();
            $table->text('signature_key')->nullable();
            $table->boolean('is_valid_signature')->default(false);
            $table->boolean('is_processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->string('process_status', 50)->default('pending');
            $table->text('process_message')->nullable();
            $table->json('raw_payload');
            $table->timestamp('created_at')->nullable();

            $table->index('provider_order_id');
            $table->index('provider_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhooks');
    }
};
