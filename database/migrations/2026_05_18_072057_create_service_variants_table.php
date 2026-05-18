<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->string('unit', 50);
            $table->decimal('min_quantity', 10, 2)->default(1);
            $table->integer('estimated_duration_hours')->nullable();
            $table->boolean('is_express')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('outlet_id');
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_variants');
    }
};
