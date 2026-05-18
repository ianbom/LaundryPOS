<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOutletRequest;
use App\Http\Requests\UpdateOutletRequest;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OutletController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeOwner($request);

        $outlets = Outlet::query()
            ->when($request->string('search')->toString() !== '', function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->string('status')->toString() === 'active');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('outlets/index', [
            'outlets' => $outlets,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeOwner($request);

        return Inertia::render('outlets/create');
    }

    public function store(StoreOutletRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            if ($request->boolean('is_main')) {
                Outlet::query()->update(['is_main' => false]);
            }

            Outlet::query()->create([
                ...$request->validated(),
                'is_main' => $request->boolean('is_main'),
                'is_active' => $request->boolean('is_active', true),
            ]);
        });

        return to_route('outlets.index')->with('success', 'Outlet created.');
    }

    public function show(Request $request, Outlet $outlet): Response
    {
        $this->authorizeOwner($request);

        return Inertia::render('outlets/show', [
            'outlet' => $outlet,
        ]);
    }

    public function edit(Request $request, Outlet $outlet): Response
    {
        $this->authorizeOwner($request);

        return Inertia::render('outlets/edit', [
            'outlet' => $outlet,
        ]);
    }

    public function update(UpdateOutletRequest $request, Outlet $outlet): RedirectResponse
    {
        DB::transaction(function () use ($request, $outlet) {
            if ($request->boolean('is_main')) {
                Outlet::query()->whereKeyNot($outlet->id)->update(['is_main' => false]);
            }

            $outlet->update([
                ...$request->validated(),
                'is_main' => $request->boolean('is_main'),
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return to_route('outlets.show', $outlet)->with('success', 'Outlet updated.');
    }

    public function destroy(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOwner($request);

        if (Outlet::query()->whereKeyNot($outlet->id)->where('is_active', true)->doesntExist()) {
            return back()->withErrors(['outlet' => 'At least one active outlet is required.']);
        }

        $outlet->delete();

        return to_route('outlets.index')->with('success', 'Outlet deleted.');
    }

    public function toggleActive(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOwner($request);

        if ($outlet->is_active && Outlet::query()->whereKeyNot($outlet->id)->where('is_active', true)->doesntExist()) {
            return back()->withErrors(['outlet' => 'At least one active outlet is required.']);
        }

        $outlet->update(['is_active' => ! $outlet->is_active]);

        return back()->with('success', 'Outlet status updated.');
    }

    public function setMain(Request $request, Outlet $outlet): RedirectResponse
    {
        $this->authorizeOwner($request);

        DB::transaction(function () use ($outlet) {
            Outlet::query()->whereKeyNot($outlet->id)->update(['is_main' => false]);
            $outlet->update(['is_main' => true, 'is_active' => true]);
        });

        return back()->with('success', 'Main outlet updated.');
    }

    private function authorizeOwner(Request $request): void
    {
        abort_unless($request->user()?->global_role === 'owner', 403);
    }
}
