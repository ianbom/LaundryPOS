<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->global_role === 'owner';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('outlets', 'code')->ignore($this->route('outlet'))],
            'slug' => ['required', 'string', 'max:150', Rule::unique('outlets', 'slug')->ignore($this->route('outlet'))],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string'],
            'google_maps_url' => ['nullable', 'url'],
            'is_main' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
