<?php

namespace App\Http\Requests;

use App\Support\OutletAccess;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return OutletAccess::canManageServices($this->user(), (int) $this->route('service')->outlet_id);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'min_quantity' => ['required', 'numeric', 'min:0.01'],
            'estimated_duration_hours' => ['nullable', 'integer', 'min:1'],
            'is_express' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
