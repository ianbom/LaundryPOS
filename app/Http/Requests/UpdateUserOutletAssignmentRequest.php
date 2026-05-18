<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserOutletAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->global_role === 'owner';
    }

    public function rules(): array
    {
        return [
            'outlets' => ['required', 'array'],
            'outlets.*.outlet_id' => ['nullable', 'exists:outlets,id'],
            'outlets.*.role' => ['nullable', Rule::in(['owner', 'admin', 'cashier', 'staff'])],
            'outlets.*.can_manage_orders' => ['boolean'],
            'outlets.*.can_manage_payments' => ['boolean'],
            'outlets.*.can_manage_services' => ['boolean'],
            'outlets.*.can_manage_reports' => ['boolean'],
            'outlets.*.can_manage_users' => ['boolean'],
            'outlets.*.can_manage_settings' => ['boolean'],
            'outlets.*.is_primary' => ['boolean'],
            'outlets.*.is_active' => ['boolean'],
        ];
    }
}
