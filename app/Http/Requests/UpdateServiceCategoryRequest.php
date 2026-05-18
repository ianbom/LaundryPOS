<?php

namespace App\Http\Requests;

use App\Support\OutletAccess;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return OutletAccess::canManageServices($this->user(), (int) $this->route('service_category')->outlet_id)
            && $this->integer('outlet_id') > 0
            && OutletAccess::canManageServices($this->user(), $this->integer('outlet_id'));
    }

    public function rules(): array
    {
        return [
            'outlet_id' => ['required', 'exists:outlets,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
