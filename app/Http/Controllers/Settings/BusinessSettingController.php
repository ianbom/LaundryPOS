<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateBusinessSettingRequest;
use App\Support\BusinessSettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BusinessSettingController extends Controller
{
    public function edit(): Response
    {
        abort_unless(request()->user()?->global_role === 'owner', 403);

        return Inertia::render('settings/business/edit', [
            'businessSetting' => BusinessSettings::current(),
        ]);
    }

    public function update(UpdateBusinessSettingRequest $request): RedirectResponse
    {
        $businessSetting = BusinessSettings::current();
        $data = $request->safe()->except(['logo_path', 'favicon_path']);

        if ($request->hasFile('logo_path')) {
            $data['logo_path'] = $request->file('logo_path')->store('business', 'public');
        }

        if ($request->hasFile('favicon_path')) {
            $data['favicon_path'] = $request->file('favicon_path')->store('business', 'public');
        }

        $businessSetting->update($data);

        return to_route('settings.business.edit')->with('success', 'Business settings updated.');
    }
}
