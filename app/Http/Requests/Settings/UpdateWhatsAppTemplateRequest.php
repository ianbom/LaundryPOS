<?php

namespace App\Http\Requests\Settings;

use App\Support\OutletAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWhatsAppTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return OutletAccess::canManageSettings($this->user(), $this->integer('outlet_id') ?: null);
    }

    public function rules(): array
    {
        return [
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'type' => ['required', 'in:payment_receipt,order_created,order_processing,order_ready,order_completed,payment_reminder,custom'],
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->sometimes('type', [
            Rule::unique('whatsapp_templates', 'type')
                ->ignore($this->route('template')->id)
                ->where('outlet_id', $this->input('outlet_id'))
                ->where('is_active', true),
        ], fn () => $this->boolean('is_active', true) && $this->input('type') !== 'custom');
    }
}
