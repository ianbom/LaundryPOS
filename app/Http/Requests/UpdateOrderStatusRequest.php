<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                'waiting_payment',
                'received',
                'processing',
                'washing',
                'drying',
                'ironing',
                'ready_to_pickup',
                'delivering',
                'completed',
                'cancelled',
            ])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
