<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_product_with_active_true_by_default(): void
    {
        $service = app(ProductService::class);

        $product = $service->create([
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 100,
            'stock' => 10,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'active' => true,
        ]);
    }
}
