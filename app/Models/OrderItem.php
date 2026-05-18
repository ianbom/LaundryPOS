<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'service_category_id',
        'service_id',
        'service_variant_id',
        'service_name',
        'variant_name',
        'pricing_type',
        'unit',
        'quantity',
        'charged_quantity',
        'unit_price',
        'subtotal',
        'notes',
    ];

    protected $attributes = [
        'quantity' => 1,
        'charged_quantity' => 1,
        'unit_price' => 0,
        'subtotal' => 0,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceVariant(): BelongsTo
    {
        return $this->belongsTo(ServiceVariant::class);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'charged_quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }
}
