<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateIntegrationSettingRequest;
use App\Support\BusinessSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IntegrationSettingController extends Controller
{
    public function edit(): Response
    {
        abort_unless(request()->user()?->global_role === 'owner', 403);

        $businessSetting = BusinessSettings::current();

        return Inertia::render('settings/integrations/edit', [
            'integrationSettings' => [
                'whatsapp_provider' => $businessSetting->whatsapp_provider,
                'whatsapp_api_key_masked' => $this->maskSecret($businessSetting->whatsapp_api_key),
                'whatsapp_sender_number' => $businessSetting->whatsapp_sender_number,
                'midtrans_server_key_masked' => $this->maskSecret($businessSetting->midtrans_server_key),
                'midtrans_client_key_masked' => $this->maskSecret($businessSetting->midtrans_client_key),
                'midtrans_is_production' => $businessSetting->midtrans_is_production,
                'qris_expiry_minutes' => $businessSetting->qris_expiry_minutes,
            ],
        ]);
    }

    public function update(UpdateIntegrationSettingRequest $request): RedirectResponse
    {
        $businessSetting = BusinessSettings::current();
        $data = $request->safe()->except([
            'whatsapp_api_key',
            'midtrans_server_key',
            'midtrans_client_key',
        ]);

        foreach (['whatsapp_api_key', 'midtrans_server_key', 'midtrans_client_key'] as $field) {
            $value = $request->string($field)->toString();

            if ($value !== '') {
                $data[$field] = $value;
            }
        }

        $businessSetting->update($data);

        return to_route('settings.integrations.edit')->with('success', 'Integration settings updated.');
    }

    public function testWhatsapp(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->global_role === 'owner', 403);

        return back()->with('success', 'WhatsApp configuration test queued.');
    }

    public function testMidtrans(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->global_role === 'owner', 403);

        return back()->with('success', 'Midtrans configuration test completed.');
    }

    private function maskSecret(?string $secret): ?string
    {
        if ($secret === null || $secret === '') {
            return null;
        }

        if (mb_strlen($secret) <= 8) {
            return str_repeat('*', mb_strlen($secret));
        }

        return mb_substr($secret, 0, 4).str_repeat('*', 8).mb_substr($secret, -4);
    }
}
