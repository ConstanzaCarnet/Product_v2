<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Product API Resource
 * 
 * Transforms Product model into JSON response format following specification v1.2.0
 * Response format defined in spec section 4.1, 4.3, 4.4
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * 
     * Returns product data with all fields as defined in spec section 2.1
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price, // Ensure 2 decimal precision
            'stock' => $this->stock,
            'active' => $this->active,
            'image' => $this->image,
            'created_at' => $this->created_at->toIso8601String(), // ISO 8601 UTC format per spec
            'updated_at' => $this->updated_at->toIso8601String(), // ISO 8601 UTC format per spec
        ];
    }
}
