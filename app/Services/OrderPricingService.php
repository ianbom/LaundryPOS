<?php

namespace App\Services;

use App\Models\ServiceVariant;

class OrderPricingService
{
    /**
     * @return array<string, mixed>
     */
    public function calculateItem(ServiceVariant $serviceVariant, int|float|string $quantity): array
    {
        $serviceVariant->loadMissing('service.serviceCategory');

        $service = $serviceVariant->service;
        $category = $service->serviceCategory;
        $quantity = max((float) $quantity, 0.01);
        $unitPrice = (float) $serviceVariant->price;
        $minQuantity = max((float) $serviceVariant->min_quantity, 0.01);
        $chargedQuantity = max($quantity, $minQuantity);

        return [
            'service_category_id' => $category->id,
            'service_id' => $service->id,
            'service_variant_id' => $serviceVariant->id,
            'service_name' => $service->name,
            'variant_name' => $serviceVariant->name,
            'pricing_type' => $service->pricing_type,
            'unit' => $serviceVariant->unit,
            'quantity' => round($quantity, 2),
            'charged_quantity' => round($chargedQuantity, 2),
            'unit_price' => round($unitPrice, 2),
            'subtotal' => round($chargedQuantity * $unitPrice, 2),
        ];
    }

    /**
     * @param  array<int, array{subtotal: numeric-string|int|float}>  $items
     * @return array<string, float>
     */
    public function calculateOrderTotals(array $items, int|float|string $discountAmount = 0, int|float|string $additionalFee = 0, int|float|string $deliveryFee = 0): array
    {
        $subtotal = array_reduce(
            $items,
            fn (float $carry, array $item): float => $carry + (float) $item['subtotal'],
            0.0,
        );
        $discountAmount = max((float) $discountAmount, 0.0);
        $additionalFee = max((float) $additionalFee, 0.0);
        $deliveryFee = max((float) $deliveryFee, 0.0);
        $grandTotal = max($subtotal - $discountAmount + $additionalFee + $deliveryFee, 0.0);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'additional_fee' => round($additionalFee, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }
}
