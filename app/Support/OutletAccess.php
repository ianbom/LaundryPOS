<?php

namespace App\Support;

use App\Models\Outlet;
use App\Models\User;

class OutletAccess
{
    /**
     * @return array<int>
     */
    public static function accessibleOutletIds(User $user): array
    {
        if ($user->global_role === 'owner') {
            return Outlet::query()->pluck('id')->all();
        }

        return $user->userOutlets()
            ->where('is_active', true)
            ->pluck('outlet_id')
            ->all();
    }

    public static function canAccessOutlet(User $user, int $outletId): bool
    {
        if ($user->global_role === 'owner') {
            return true;
        }

        return $user->userOutlets()
            ->where('outlet_id', $outletId)
            ->where('is_active', true)
            ->exists();
    }

    public static function activeOutletId(User $user): ?int
    {
        $sessionOutletId = session('current_outlet_id');

        if ($sessionOutletId && self::canAccessOutlet($user, (int) $sessionOutletId)) {
            return (int) $sessionOutletId;
        }

        if ($user->global_role === 'owner') {
            return Outlet::query()
                ->where('is_active', true)
                ->orderByDesc('is_main')
                ->orderBy('name')
                ->value('id');
        }

        return $user->userOutlets()
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->value('outlet_id');
    }

    public static function canManageOrders(User $user, int $outletId): bool
    {
        return self::hasPermission($user, $outletId, 'can_manage_orders');
    }

    public static function canManageServices(User $user, int $outletId): bool
    {
        return self::hasPermission($user, $outletId, 'can_manage_services');
    }

    public static function canManageSettings(User $user, ?int $outletId = null): bool
    {
        if ($user->global_role === 'owner') {
            return true;
        }

        $query = $user->userOutlets()
            ->where('is_active', true)
            ->where('can_manage_settings', true);

        if ($outletId !== null) {
            $query->where('outlet_id', $outletId);
        }

        return $query->exists();
    }

    private static function hasPermission(User $user, int $outletId, string $permission): bool
    {
        if ($user->global_role === 'owner') {
            return true;
        }

        return $user->userOutlets()
            ->where('outlet_id', $outletId)
            ->where('is_active', true)
            ->where($permission, true)
            ->exists();
    }
}
