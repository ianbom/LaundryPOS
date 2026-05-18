<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIntegrationSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->global_role === 'owner';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'whatsapp_provider' => ['nullable', 'string', 'max:50'],
            'whatsapp_api_key' => ['nullable', 'string'],
            'whatsapp_sender_number' => ['nullable', 'string', 'max:30'],
            'midtrans_server_key' => ['nullable', 'string'],
            'midtrans_client_key' => ['nullable', 'string'],
            'midtrans_is_production' => ['boolean'],
            'qris_expiry_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ];
    }
}
