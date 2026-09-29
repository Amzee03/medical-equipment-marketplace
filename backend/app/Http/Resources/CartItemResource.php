<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'cart_id'             => $this->cart_id,
            'product'             => new ProductResource($this->whenLoaded('product')),
            'type'                => $this->type,
            'quantity'            => $this->quantity,
            'rental_start_date'   => $this->rental_start_date ? $this->rental_start_date->format('Y-m-d') : null,
            'rental_end_date'     => $this->rental_end_date ? $this->rental_end_date->format('Y-m-d') : null,
            'rental_pricing_tier' => $this->rental_pricing_tier,
            // Custom computed properties (populated in controller)
            'rental_price_breakdown' => $this->when(isset($this->rental_price_breakdown), $this->rental_price_breakdown),
            'available_units_count'  => $this->when(isset($this->available_units_count), $this->available_units_count),
        ];
    }
}
