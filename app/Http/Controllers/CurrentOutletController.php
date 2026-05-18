<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Support\OutletAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CurrentOutletController extends Controller
{
    public function show(Request $request): array
    {
        $user = $request->user();

        abort_if($user === null, 403);

        return [
            'current_outlet_id' => OutletAccess::activeOutletId($user),
            'outlets' => Outlet::query()
                ->whereIn('id', OutletAccess::accessibleOutletIds($user))
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'is_main']),
        ];
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $validated = $request->validate([
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
        ]);

        if (! OutletAccess::canAccessOutlet($user, (int) $validated['outlet_id'])) {
            throw ValidationException::withMessages([
                'outlet_id' => 'Selected outlet is not accessible.',
            ]);
        }

        session(['current_outlet_id' => (int) $validated['outlet_id']]);

        return back()->with('success', 'Current outlet updated.');
    }
}
