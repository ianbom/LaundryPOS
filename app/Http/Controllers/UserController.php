<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetUserPasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeOwner($request);

        $users = User::query()
            ->withCount('userOutlets')
            ->when($request->string('search')->toString() !== '', function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('global_role', $request->string('role')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('users/index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeOwner($request);

        return Inertia::render('users/create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return to_route('users.index')->with('success', 'User created.');
    }

    public function show(Request $request, User $user): Response
    {
        $this->authorizeOwner($request);

        return Inertia::render('users/show', [
            'managedUser' => $user->load('userOutlets.outlet'),
        ]);
    }

    public function edit(Request $request, User $user): Response
    {
        $this->authorizeOwner($request);

        return Inertia::render('users/edit', [
            'managedUser' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('users.show', $user)->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeOwner($request);

        if ($this->isLastActiveOwner($user)) {
            return back()->withErrors(['user' => 'Last active owner cannot be deleted.']);
        }

        $user->delete();

        return to_route('users.index')->with('success', 'User deleted.');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $this->authorizeOwner($request);

        if ($user->is_active && $this->isLastActiveOwner($user)) {
            return back()->withErrors(['user' => 'Last active owner cannot be deactivated.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'User status updated.');
    }

    public function resetPassword(ResetUserPasswordRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'password' => $request->validated('password'),
        ]);

        return to_route('users.show', $user)->with('success', 'Password reset.');
    }

    private function authorizeOwner(Request $request): void
    {
        abort_unless($request->user()?->global_role === 'owner', 403);
    }

    private function isLastActiveOwner(User $user): bool
    {
        return $user->global_role === 'owner'
            && User::query()
                ->where('global_role', 'owner')
                ->where('is_active', true)
                ->whereKeyNot($user->id)
                ->doesntExist();
    }
}
