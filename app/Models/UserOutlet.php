<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserOutlet extends Pivot
{
    protected $table = 'user_outlets';

    protected $fillable = [
        'user_id',
        'outlet_id',
        'role',
        'can_manage_orders',
        'can_manage_payments',
        'can_manage_services',
        'can_manage_reports',
        'can_manage_users',
        'can_manage_settings',
        'is_primary',
        'is_active',
    ];

    protected $attributes = [
        'role' => 'staff',
        'can_manage_orders' => true,
        'can_manage_payments' => true,
        'can_manage_services' => false,
        'can_manage_reports' => false,
        'can_manage_users' => false,
        'can_manage_settings' => false,
        'is_primary' => false,
        'is_active' => true,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    protected function casts(): array
    {
        return [
            'can_manage_orders' => 'boolean',
            'can_manage_payments' => 'boolean',
            'can_manage_services' => 'boolean',
            'can_manage_reports' => 'boolean',
            'can_manage_users' => 'boolean',
            'can_manage_settings' => 'boolean',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
