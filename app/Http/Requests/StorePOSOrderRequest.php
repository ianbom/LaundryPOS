<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\ServiceVariant;
use App\Support\OutletAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePOSOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->integer('outlet_id') > 0
            && OutletAccess::canManageOrders($this->user(), $this->integer('outlet_id'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'outlet_id' => ['required', 'exists:outlets,id'],
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where(fn ($query) => $query->where('outlet_id', $this->integer('outlet_id'))),
            ],
            'customer' => ['required_without:customer_id', 'array'],
            'customer.name' => ['required_without:customer_id', 'nullable', 'string', 'max:150'],
            'customer.phone' => ['required_without:customer_id', 'nullable', 'string', 'max:30'],
            'customer.whatsapp_number' => ['nullable', 'string', 'max:30'],
            'customer.address' => ['nullable', 'string'],
            'customer.notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_variant_id' => [
                'required',
                Rule::exists('service_variants', 'id')->where(function ($query) {
                    $query->where('outlet_id', $this->integer('outlet_id'))
                        ->where('is_active', true)
                        ->whereNull('deleted_at');
                }),
            ],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.notes' => ['nullable', 'string'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'additional_fee' => ['nullable', 'numeric', 'min:0'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'customer_notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'estimated_done_at' => ['nullable', 'date'],
        ];
    }

    public function customer(): Customer
    {
        if ($this->filled('customer_id')) {
            return Customer::query()->findOrFail($this->integer('customer_id'));
        }

        $customer = $this->array('customer');

        return Customer::query()->create([
            'outlet_id' => $this->integer('outlet_id'),
            'name' => $customer['name'],
            'phone' => $customer['phone'],
            'whatsapp_number' => ($customer['whatsapp_number'] ?? null) ?: $customer['phone'],
            'address' => $customer['address'] ?? null,
            'notes' => $customer['notes'] ?? null,
        ]);
    }

    /**
     * @return array<int, ServiceVariant>
     */
    public function serviceVariants(): array
    {
        return ServiceVariant::query()
            ->with('service.serviceCategory')
            ->whereIn('id', collect($this->input('items', []))->pluck('service_variant_id')->all())
            ->get()
            ->keyBy('id')
            ->all();
    }
}
