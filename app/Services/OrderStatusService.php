<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Support\OutletAccess;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $forwardTransitions = [
        'waiting_payment' => ['received'],
        'received' => ['processing'],
        'processing' => ['washing', 'ready_to_pickup'],
        'washing' => ['drying'],
        'drying' => ['ironing'],
        'ironing' => ['ready_to_pickup'],
        'ready_to_pickup' => ['completed'],
    ];

    public function canManageOrder(User $user, Order $order): bool
    {
        if ($user->global_role === 'owner') {
            return true;
        }

        return $user->userOutlets()
            ->where('outlet_id', $order->outlet_id)
            ->where('is_active', true)
            ->where('can_manage_orders', true)
            ->exists();
    }

    public function canManageReports(User $user, ?int $outletId = null): bool
    {
        if (in_array($user->global_role, ['owner', 'admin'], true)) {
            return $outletId === null || OutletAccess::canAccessOutlet($user, $outletId);
        }

        $query = $user->userOutlets()
            ->where('is_active', true)
            ->where('can_manage_reports', true);

        if ($outletId !== null) {
            $query->where('outlet_id', $outletId);
        }

        return $query->exists();
    }

    public function ensureTransitionAllowed(User $user, Order $order, string $newStatus): void
    {
        if (in_array($user->global_role, ['owner', 'admin'], true)) {
            return;
        }

        if ($newStatus === 'cancelled') {
            return;
        }

        $expected = $this->forwardTransitions[$order->order_status] ?? [];

        if (! in_array($newStatus, $expected, true)) {
            throw ValidationException::withMessages([
                'status' => 'Status transition is not allowed.',
            ]);
        }
    }
}
