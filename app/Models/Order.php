<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'customer_id',
        'created_by',
        'invoice_number',
        'order_status',
        'payment_status',
        'active_payment_id',
        'order_date',
        'estimated_done_at',
        'completed_at',
        'cancelled_at',
        'subtotal',
        'discount_amount',
        'additional_fee',
        'delivery_fee',
        'grand_total',
        'customer_notes',
        'internal_notes',
        'tracking_token',
    ];

    protected $attributes = [
        'order_status' => 'waiting_payment',
        'payment_status' => 'unpaid',
        'subtotal' => 0,
        'discount_amount' => 0,
        'additional_fee' => 0,
        'delivery_fee' => 0,
        'grand_total' => 0,
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activePayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'active_payment_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentWebhooks(): HasMany
    {
        return $this->hasMany(PaymentWebhook::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    protected function casts(): array
    {
        return [
            'order_date' => 'datetime',
            'estimated_done_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'additional_fee' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }
}
