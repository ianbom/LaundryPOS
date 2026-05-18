<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentWebhook extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'order_id',
        'provider',
        'provider_order_id',
        'provider_transaction_id',
        'event_type',
        'transaction_status',
        'fraud_status',
        'payment_type',
        'gross_amount',
        'signature_key',
        'is_valid_signature',
        'is_processed',
        'processed_at',
        'process_status',
        'process_message',
        'raw_payload',
        'created_at',
    ];

    protected $hidden = [
        'signature_key',
    ];

    protected $attributes = [
        'is_valid_signature' => false,
        'is_processed' => false,
        'process_status' => 'pending',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'is_valid_signature' => 'boolean',
            'is_processed' => 'boolean',
            'processed_at' => 'datetime',
            'raw_payload' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
