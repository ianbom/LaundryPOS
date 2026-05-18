<?php

namespace App\Http\Requests;

use App\Support\OutletAccess;
use Illuminate\Foundation\Http\FormRequest;

class GenerateQrisRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order !== null
            && OutletAccess::canManagePayments($this->user(), (int) $order->outlet_id);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string'],
        ];
    }
}
