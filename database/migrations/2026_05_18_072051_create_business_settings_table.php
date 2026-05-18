<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name', 150);
            $table->string('business_slug', 150)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('owner_name', 150)->nullable();
            $table->string('owner_phone', 30)->nullable();
            $table->string('owner_email', 150)->nullable();
            $table->string('default_phone', 30)->nullable();
            $table->string('default_whatsapp_number', 30)->nullable();
            $table->string('default_email', 150)->nullable();
            $table->text('default_address')->nullable();
            $table->text('default_google_maps_url')->nullable();
            $table->string('timezone', 100)->default('Asia/Jakarta');
            $table->string('currency', 10)->default('IDR');
            $table->text('receipt_footer_text')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->integer('qris_expiry_minutes')->default(30);
            $table->string('whatsapp_provider', 50)->nullable();
            $table->text('whatsapp_api_key')->nullable();
            $table->string('whatsapp_sender_number', 30)->nullable();
            $table->text('midtrans_server_key')->nullable();
            $table->text('midtrans_client_key')->nullable();
            $table->boolean('midtrans_is_production')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
