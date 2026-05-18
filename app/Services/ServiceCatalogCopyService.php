<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceVariant;
use Illuminate\Support\Facades\DB;

class ServiceCatalogCopyService
{
    /**
     * @return array{categories_created:int,categories_updated:int,services_created:int,services_updated:int,services_skipped:int,variants_created:int,variants_updated:int,variants_skipped:int}
     */
    public function copy(int $sourceOutletId, int $targetOutletId, string $copyMode, bool $includeInactive): array
    {
        return DB::transaction(function () use ($sourceOutletId, $targetOutletId, $copyMode, $includeInactive) {
            $result = [
                'categories_created' => 0,
                'categories_updated' => 0,
                'services_created' => 0,
                'services_updated' => 0,
                'services_skipped' => 0,
                'variants_created' => 0,
                'variants_updated' => 0,
                'variants_skipped' => 0,
            ];

            $categories = ServiceCategory::query()
                ->with(['services' => fn ($query) => $this->activeScope($query, $includeInactive)
                    ->with(['variants' => fn ($variantQuery) => $this->activeScope($variantQuery, $includeInactive)])
                    ->orderBy('sort_order')
                    ->orderBy('name')])
                ->where('outlet_id', $sourceOutletId)
                ->when(! $includeInactive, fn ($query) => $query->where('is_active', true))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            foreach ($categories as $category) {
                $targetCategory = $this->copyCategory($category, $targetOutletId, $copyMode, $result);

                foreach ($category->services as $service) {
                    $targetService = $this->copyService($service, $targetCategory, $copyMode, $result);

                    if (! $targetService) {
                        continue;
                    }

                    foreach ($service->variants as $variant) {
                        $this->copyVariant($variant, $targetService, $copyMode, $result);
                    }
                }
            }

            return $result;
        });
    }

    private function copyCategory(ServiceCategory $category, int $targetOutletId, string $copyMode, array &$result): ServiceCategory
    {
        $attributes = [
            'name' => $category->name,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
            'is_active' => $category->is_active,
        ];

        if ($copyMode === 'duplicate_all') {
            $result['categories_created']++;

            return ServiceCategory::query()->create([
                ...$attributes,
                'outlet_id' => $targetOutletId,
                'name' => $this->copyName($category->name),
            ]);
        }

        $target = ServiceCategory::query()
            ->where('outlet_id', $targetOutletId)
            ->where('name', $category->name)
            ->first();

        if (! $target) {
            $result['categories_created']++;

            return ServiceCategory::query()->create([...$attributes, 'outlet_id' => $targetOutletId]);
        }

        if ($copyMode === 'overwrite_existing') {
            $target->update($attributes);
            $result['categories_updated']++;
        }

        return $target;
    }

    private function copyService(Service $service, ServiceCategory $targetCategory, string $copyMode, array &$result): ?Service
    {
        $attributes = [
            'description' => $service->description,
            'pricing_type' => $service->pricing_type,
            'is_active' => $service->is_active,
            'sort_order' => $service->sort_order,
        ];

        if ($copyMode === 'duplicate_all') {
            $result['services_created']++;

            return Service::query()->create([
                ...$attributes,
                'outlet_id' => $targetCategory->outlet_id,
                'service_category_id' => $targetCategory->id,
                'name' => $this->copyName($service->name),
            ]);
        }

        $target = Service::query()
            ->where('outlet_id', $targetCategory->outlet_id)
            ->where('service_category_id', $targetCategory->id)
            ->where('name', $service->name)
            ->first();

        if (! $target) {
            $result['services_created']++;

            return Service::query()->create([
                ...$attributes,
                'outlet_id' => $targetCategory->outlet_id,
                'service_category_id' => $targetCategory->id,
                'name' => $service->name,
            ]);
        }

        if ($copyMode === 'skip_existing') {
            $result['services_skipped']++;

            return null;
        }

        $target->update($attributes);
        $result['services_updated']++;

        return $target;
    }

    private function copyVariant(ServiceVariant $variant, Service $targetService, string $copyMode, array &$result): void
    {
        $attributes = [
            'description' => $variant->description,
            'price' => $variant->price,
            'unit' => $variant->unit,
            'min_quantity' => $variant->min_quantity,
            'estimated_duration_hours' => $variant->estimated_duration_hours,
            'is_express' => $variant->is_express,
            'is_active' => $variant->is_active,
            'sort_order' => $variant->sort_order,
        ];

        if ($copyMode === 'duplicate_all') {
            ServiceVariant::query()->create([
                ...$attributes,
                'outlet_id' => $targetService->outlet_id,
                'service_id' => $targetService->id,
                'name' => $this->copyName($variant->name),
            ]);
            $result['variants_created']++;

            return;
        }

        $target = ServiceVariant::query()
            ->where('service_id', $targetService->id)
            ->where('name', $variant->name)
            ->first();

        if (! $target) {
            ServiceVariant::query()->create([
                ...$attributes,
                'outlet_id' => $targetService->outlet_id,
                'service_id' => $targetService->id,
                'name' => $variant->name,
            ]);
            $result['variants_created']++;

            return;
        }

        if ($copyMode === 'skip_existing') {
            $result['variants_skipped']++;

            return;
        }

        $target->update($attributes);
        $result['variants_updated']++;
    }

    private function activeScope($query, bool $includeInactive)
    {
        return $query->when(! $includeInactive, fn ($query) => $query->where('is_active', true));
    }

    private function copyName(string $name): string
    {
        return "{$name} Copy";
    }
}
