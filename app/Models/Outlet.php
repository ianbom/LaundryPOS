<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'slug',
        'phone',
        'whatsapp_number',
        'email',
        'address',
        'google_maps_url',
        'is_main',
        'is_active',
    ];

    protected $attributes = [
        'is_main' => false,
        'is_active' => true,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_outlets')
            ->using(UserOutlet::class)
            ->withPivot([
                'role',
                'can_manage_orders',
                'can_manage_payments',
                'can_manage_services',
                'can_manage_reports',
                'can_manage_users',
                'can_manage_settings',
                'is_primary',
                'is_active',
            ])
            ->withTimestamps();
    }

    public function userOutlets(): HasMany
    {
        return $this->hasMany(UserOutlet::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function serviceVariants(): HasMany
    {
        return $this->hasMany(ServiceVariant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function whatsappTemplates(): HasMany
    {
        return $this->hasMany(WhatsappTemplate::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
