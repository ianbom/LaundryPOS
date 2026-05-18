<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'outlet_id',
        'order_id',
        'provider',
        'method',
        'status',
        'is_active',
        'amount',
        'amount_paid',
        'change_amount',
        'provider_order_id',
        'provider_transaction_id',
        'provider_reference_id',
        'qris_string',
        'qris_url',
        'payment_url',
        'expired_at',
        'paid_at',
        'cancelled_at',
        'confirmed_by',
        'raw_response',
    ];

    protected $attributes = [
        'status' => 'pending',
        'is_active' => true,
        'amount' => 0,
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function activeForOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'active_payment_id');
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(PaymentWebhook::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'expired_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'raw_response' => 'array',
        ];
    }
}
