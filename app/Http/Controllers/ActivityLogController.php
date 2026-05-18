<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\OrderStatusService;
use App\Support\OutletAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request, OrderStatusService $statusService): Response
    {
        $user = $request->user();
        abort_unless($user->global_role === 'owner' || $statusService->canManageReports($user), 403);

        $outletIds = OutletAccess::accessibleOutletIds($user);

        $logs = ActivityLog::query()
            ->with(['outlet:id,name', 'user:id,name'])
            ->where(function ($query) use ($outletIds) {
                $query->whereNull('outlet_id')
                    ->orWhereIn('outlet_id', $outletIds);
            })
            ->when($request->filled('outlet_id'), fn ($query) => $query->where('outlet_id', $request->integer('outlet_id')))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')->toString()))
            ->when($request->filled('subject_type'), fn ($query) => $query->where('subject_type', $request->string('subject_type')->toString()))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('activity-logs/index', [
            'logs' => $logs,
            'filters' => $request->only(['outlet_id', 'user_id', 'action', 'subject_type', 'date_from', 'date_to']),
        ]);
    }
}
