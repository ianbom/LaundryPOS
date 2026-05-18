<?php

namespace App\Http\Requests;

use App\Models\ServiceCategory;
use App\Support\OutletAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return OutletAccess::canManageServices($this->user(), (int) $this->route('service')->outlet_id)
            && $this->integer('outlet_id') > 0
            && OutletAccess::canManageServices($this->user(), $this->integer('outlet_id'));
    }

    public function rules(): array
    {
        return [
            'outlet_id' => ['required', 'exists:outlets,id'],
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'pricing_type' => ['required', 'in:per_kg,per_item,per_set,per_m2,fixed,custom'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $category = ServiceCategory::query()->find($this->integer('service_category_id'));

                if ($category && $category->outlet_id !== $this->integer('outlet_id')) {
                    $validator->errors()->add('service_category_id', 'Service category must belong to the selected outlet.');
                }
            },
        ];
    }
}
