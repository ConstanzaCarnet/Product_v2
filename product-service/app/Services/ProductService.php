<?php

namespace App\Services;
use Illuminate\Validation\ValidationException;
use App\Models\Product;

class ProductService

{
    // Constants moved from controller
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_LIMIT = 10;
    private const MAX_LIMIT = 100;

    public function create(array $data): Product
    {
        // Business rule: active by default (spec section 2.2)
        $data['active'] = true;

        return Product::create($data);
    }

    public function delete(Product $product): void
    {
        //validation stock
        if ($product->stock > 0) {
            throw ValidationException::withMessages([
                'stock' => 'Cannot delete a product with stock greater than 0',
            ]);
        }
    
        $product->delete();
    }
    

    public function list(array $params): array
    {
        // Default values
        $page   = $params['page']  ?? self::DEFAULT_PAGE;
        $limit  = $params['limit'] ?? self::DEFAULT_LIMIT;
        $active = array_key_exists('active', $params)
            ? filter_var($params['active'], FILTER_VALIDATE_BOOLEAN)
            : true;

        $sort  = $params['sort']  ?? 'id';
        $order = $params['order'] ?? 'asc';

        // Build query
        $query = Product::query();

        $query->where('active', $active);

        // Price filters
        if (isset($params['min_price'])) {
            $query->where('price', '>=', $params['min_price']);
        }

        if (isset($params['max_price'])) {
            $query->where('price', '<=', $params['max_price']);
        }

        // Stock filter
        if (isset($params['stock_min'])) {
            $query->where('stock', '>=', $params['stock_min']);
        }

        // Search filter
        if (isset($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $query->orderBy($sort, $order);

        // Pagination
        $total = $query->count();
        $products = $query
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $totalPages = (int) ceil($total / $limit);

        return [
            'products' => $products,
            'meta' => [
                'page' => (int) $page,
                'limit' => (int) $limit,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ];
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        $product->refresh();

        return $product;
    }

    public function show(Product $product): Product
    {
        return $product;
    }

}
