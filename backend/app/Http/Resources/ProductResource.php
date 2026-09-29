<?php

// app/Http/Resources/ProductResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'category_id'              => $this->category_id,
            // Sertakan category jika sudah di-load (with('category'))
            'category'                 => new CategoryResource($this->whenLoaded('category')),
            'name'                     => $this->name,
            'slug'                     => $this->slug,
            'sku'                      => $this->sku,
            'description'              => $this->description,
            'function'                 => $this->function,
            'brand'                    => $this->brand,
            'model'                    => $this->model,
            'specifications'           => $this->specifications,
            'condition'                => $this->condition,
            // Purchase info
            'purchase_available'       => $this->purchase_available,
            'sale_price'               => $this->sale_price,
            'stock_purchase'           => $this->stock_purchase,
            // Rental info
            'rental_available'         => $this->rental_available,
            'rental_price_daily'       => $this->rental_price_daily,
            'rental_price_weekly'      => $this->rental_price_weekly,
            'rental_price_monthly'     => $this->rental_price_monthly,
            'min_rental_days'          => $this->min_rental_days,
            'max_rental_days'          => $this->max_rental_days,
            // Shipping options
            'shipping_owner_delivery'  => $this->shipping_owner_delivery,
            'shipping_express'         => $this->shipping_express,
            'shipping_regular'         => $this->shipping_regular,
            'shipping_pickup'          => $this->shipping_pickup,
            'status'                   => $this->status,
            // Computed field: jumlah unit yang siap disewa saat ini
            // Di-compute dari relasi equipmentUnits yang di-eager load
            'available_units_count'    => $this->when(
                $this->relationLoaded('equipmentUnits'),
                fn () => $this->equipmentUnits->where('status', 'available')->count()
            ),
            // Images di-sort by sort_order, primary pertama
            'images'                   => ProductImageResource::collection(
                $this->whenLoaded('images')
            ),
            'created_at'               => $this->created_at->toIso8601String(),
            'updated_at'               => $this->updated_at->toIso8601String(),
        ];
    }
}
