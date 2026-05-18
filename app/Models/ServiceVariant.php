<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'service_id',
        'name',
        'description',
        'price',
        'unit',
        'min_quantity',
        'estimated_duration_hours',
        'is_express',
        'is_active',
        'sort_order',
    ];

    protected $attributes = [
        'price' => 0,
        'min_quantity' => 1,
        'is_express' => false,
        'is_active' => true,
        'sort_order' => 0,
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'min_quantity' => 'decimal:2',
            'estimated_duration_hours' => 'integer',
            'is_express' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
