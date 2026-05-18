<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    protected $fillable = [
        'outlet_id',
        'order_id',
        'customer_id',
        'provider',
        'phone',
        'message_type',
        'message_body',
        'status',
        'provider_message_id',
        'error_message',
        'raw_response',
        'sent_at',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected function casts(): array
    {
        return [
            'raw_response' => 'array',
            'sent_at' => 'datetime',
        ];
    }
}
