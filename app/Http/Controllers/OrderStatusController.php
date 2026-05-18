<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\ActivityLogger;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class OrderStatusController extends Controller
{
    public function update(UpdateOrderStatusRequest $request, Order $order, OrderStatusService $statusService, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_unless($statusService->canManageOrder($request->user(), $order), 403);

        $validated = $request->validated();

        DB::transaction(function () use ($activityLogger, $order, $request, $statusService, $validated) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $lockedOrder->order_status;
            $newStatus = $validated['status'];

            $statusService->ensureTransitionAllowed($request->user(), $lockedOrder, $newStatus);

            if ($oldStatus === $newStatus) {
                return;
            }

            $lockedOrder->forceFill([
                'order_status' => $newStatus,
                'completed_at' => $newStatus === 'completed' ? now() : $lockedOrder->completed_at,
                'cancelled_at' => $newStatus === 'cancelled' ? now() : $lockedOrder->cancelled_at,
            ])->save();

            $lockedOrder->statusHistories()->create([
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
                'created_at' => now(),
            ]);

            $activityLogger->log(
                $request,
                'order_status_updated',
                $lockedOrder,
                $lockedOrder->outlet_id,
                ['order_status' => $oldStatus],
                ['order_status' => $newStatus],
            );
        });

        return back()->with('success', 'Order status updated.');
    }
}
