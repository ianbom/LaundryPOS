<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosPaymentIntent extends Model
{
    protected $fillable = [
        'outlet_id',
        'user_id',
        'order_id',
        'payment_id',
        'status',
        'amount',
        'order_payload',
        'order_snapshots',
        'order_totals',
        'provider_order_id',
        'provider_transaction_id',
        'provider_reference_id',
        'qris_string',
        'qris_url',
        'payment_url',
        'expired_at',
        'paid_at',
        'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'order_payload' => 'array',
            'order_snapshots' => 'array',
            'order_totals' => 'array',
            'expired_at' => 'datetime',
            'paid_at' => 'datetime',
            'raw_response' => 'array',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
