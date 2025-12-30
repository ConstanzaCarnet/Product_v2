<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProductCollection;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\StoreProductRequest;

class ProductController extends Controller
{
    

    public function __construct(
        private ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->productService->list($request->all());

        return (new ProductCollection(
            $result['products'],
            $result['meta']
        ))->response()->setStatusCode(200);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->productService->store($request->validated());
    
        return ApiResponse::success($product, 'Producto creado', 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', Product::class);

        $product = $this->productService->update($product, $request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->productService->delete($product);
    
        return ApiResponse::success(null, 'Producto eliminado', 200);
    }
    //show method
    public function show(Product $product):JsonResponse
    {
        return (new ProductResource($product))
        ->response()
        ->setStatusCode(200);
    }

}