<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAdmin = $request->user()?->isAdmin() ?? false;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'sku' => $this->sku,
            'product_type' => $this->product_type,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'unit' => $this->whenLoaded('unit', fn () => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
                'symbol' => $this->unit->symbol,
            ]),
            'base_price' => (float) $this->base_price,
            'selling_price' => (float) $this->selling_price,
            // Only expose cost price to admin
            'cost_price' => $isAdmin ? (float) $this->cost_price : null,
            'minimum_order_quantity' => (float) $this->minimum_order_quantity,
            'maximum_order_quantity' => $this->when($isAdmin, (float) $this->maximum_order_quantity),
            'stock_tracking' => $this->stock_tracking,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'status' => $this->status,
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'image_url' => $img->image_url ?? $img->image_path,
                'alt_text' => $img->alt_text,
                'is_primary' => $img->is_primary,
            ])),
            'source_summary' => $this->when(
                $this->relationLoaded('inventoryItems') && $this->inventoryItems->isNotEmpty(),
                fn () => $this->buildSourceSummary($isAdmin)
            ),
            'average_rating' => $this->whenLoaded('reviews', fn () => round($this->reviews->avg('rating'), 1)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function buildSourceSummary(bool $isAdmin): array
    {
        $item = $this->inventoryItems->first();
        $batch = $item->harvestBatch;

        if (!$batch) {
            return [];
        }

        $harvest = $batch->harvest;
        $farm = $harvest?->farm;

        return [
            'batch_code' => $batch->batch_code,
            'harvest_date' => $harvest?->harvest_date?->toDateString(),
            'district' => $farm?->district,
            'farm_name' => $farm?->farm_name,
            // Only expose farmer details to authorized users
            'farmer' => $isAdmin ? [
                'id' => $farm?->farmer?->id,
                'full_name' => $farm?->farmer?->full_name,
            ] : null,
        ];
    }
}