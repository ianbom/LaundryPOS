<?php

namespace App\Http\Requests;

use App\Support\OutletAccess;
use Illuminate\Foundation\Http\FormRequest;

class CopyServicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return OutletAccess::canAccessOutlet($this->user(), $this->integer('source_outlet_id'))
            && OutletAccess::canManageServices($this->user(), $this->integer('target_outlet_id'));
    }

    public function rules(): array
    {
        return [
            'source_outlet_id' => ['required', 'exists:outlets,id'],
            'target_outlet_id' => ['required', 'exists:outlets,id', 'different:source_outlet_id'],
            'copy_mode' => ['required', 'in:skip_existing,duplicate_all,overwrite_existing'],
            'include_inactive' => ['boolean'],
        ];
    }
}
