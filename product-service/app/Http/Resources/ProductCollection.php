<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Product Collection API Resource
 * 
 * Transforms paginated Product collection into JSON response format
 * Response format defined in spec section 4.2
 */
class ProductCollection extends ResourceCollection
{
    /**
     * Pagination metadata
     */
    protected $pagination;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  array  $pagination
     * @return void
     */
    public function __construct($resource, array $pagination = [])
    {
        parent::__construct($resource);
        $this->pagination = $pagination;
    }

    /**
     * Transform the resource collection into an array.
     * 
     * Returns products list with pagination metadata as defined in spec section 4.2
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => ProductResource::collection($this->collection),
            'pagination' => $this->pagination,
        ];
    }
}
