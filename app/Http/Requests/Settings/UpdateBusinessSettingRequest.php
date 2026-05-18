<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessSettingRequest extends FormRequest
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
            'business_name' => ['required', 'string', 'max:150'],
            'business_slug' => ['nullable', 'string', 'max:150'],
            'logo_path' => ['nullable', 'image', 'max:2048'],
            'favicon_path' => ['nullable', 'image', 'max:1024'],
            'owner_name' => ['nullable', 'string', 'max:150'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'owner_email' => ['nullable', 'email', 'max:150'],
            'default_phone' => ['nullable', 'string', 'max:30'],
            'default_whatsapp_number' => ['nullable', 'string', 'max:30'],
            'default_email' => ['nullable', 'email', 'max:150'],
            'default_address' => ['nullable', 'string'],
            'default_google_maps_url' => ['nullable', 'url'],
            'timezone' => ['required', 'string', 'max:100'],
            'currency' => ['required', 'string', 'max:10'],
            'receipt_footer_text' => ['nullable', 'string'],
            'terms_and_conditions' => ['nullable', 'string'],
            'qris_expiry_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ];
    }
}
