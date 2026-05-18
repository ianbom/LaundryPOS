<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserOutletAssignmentRequest;
use App\Models\Outlet;
use App\Models\User;
use App\Models\UserOutlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserOutletController extends Controller
{
    public function edit(Request $request, User $user): Response
    {
        abort_unless($request->user()?->global_role === 'owner', 403);

        return Inertia::render('users/assign-outlets', [
            'managedUser' => $user->load('userOutlets'),
            'outlets' => Outlet::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUserOutletAssignmentRequest $request, User $user): RedirectResponse
    {
        $assignments = collect($request->validated('outlets'))
            ->filter(fn (array $assignment) => isset($assignment['outlet_id']))
            ->values();

        if ($assignments->where('is_primary', true)->count() > 1) {
            throw ValidationException::withMessages([
                'outlets' => 'Only one primary outlet is allowed.',
            ]);
        }

        if ($user->global_role !== 'owner' && $assignments->where('is_active', true)->isEmpty()) {
            throw ValidationException::withMessages([
                'outlets' => 'User must have at least one active outlet assignment.',
            ]);
        }

        DB::transaction(function () use ($assignments, $user) {
            $outletIds = $assignments->pluck('outlet_id')->all();

            UserOutlet::query()
                ->whereBelongsTo($user)
                ->whereNotIn('outlet_id', $outletIds)
                ->delete();

            foreach ($assignments as $assignment) {
                UserOutlet::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'outlet_id' => $assignment['outlet_id'],
                    ],
                    [
                        'role' => $assignment['role'],
                        'can_manage_orders' => (bool) ($assignment['can_manage_orders'] ?? false),
                        'can_manage_payments' => (bool) ($assignment['can_manage_payments'] ?? false),
                        'can_manage_services' => (bool) ($assignment['can_manage_services'] ?? false),
                        'can_manage_reports' => (bool) ($assignment['can_manage_reports'] ?? false),
                        'can_manage_users' => (bool) ($assignment['can_manage_users'] ?? false),
                        'can_manage_settings' => (bool) ($assignment['can_manage_settings'] ?? false),
                        'is_primary' => (bool) ($assignment['is_primary'] ?? false),
                        'is_active' => (bool) ($assignment['is_active'] ?? true),
                    ],
                );
            }
        });

        return to_route('users.show', $user)->with('success', 'Outlet assignments updated.');
    }
}
