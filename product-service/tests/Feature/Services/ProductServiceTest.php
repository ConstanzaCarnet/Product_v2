<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Services\ProductService;
use App\Models\Product;
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

    public function test_it_deletes_a_product(): void
    {
        $service = app(ProductService::class);
        $product = Product::factory()->create();
        $product->stock = 0;
        $product->save();

        $service->delete($product);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_it_updates_a_product(): void
    {
        $service = app(ProductService::class);
        $product = Product::factory()->create();
        $service->update($product, [
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 200,
            'stock' => 20,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 200,
            'stock' => 20,
        ]);
    }

    public function test_it_shows_a_product(): void
    {
        $service = app(ProductService::class);
        $product = Product::factory()->create();

        $result = $service->show($product);
        $this->assertEquals($product->id, $result->id);
    }


    public function test_it_lists_products_by_defoult(): void
    {
        $service = app(ProductService::class);

        Product::factory()->create(['active' => true]);
        Product::factory()->create(['active' => false]);

        $result = $service->list([]);

        $this->assertCount(1, $result['products']);
        $this->assertTrue($result['products']->first()->active);
    }

    public function test_it_can_list_inactive_products(): void
    {
        $service = app(ProductService::class);

        Product::factory()->create(['active' => false]);

        $result = $service->list(['active' => false]);

        $this->assertCount(1, $result['products']);
    }

    public function test_it_filters_by_min_price(): void
    {
        $service = app(ProductService::class);

        Product::factory()->create(['price' => 100]);
        Product::factory()->create(['price' => 200]);

        //filtro en el list
        $result = $service->list(['min_price' => 100]);

        $this->assertCount(1,$result['products']);
        $this->assertEquals(100, $result['products']->first()->price);
    }
    //looking for name
    public function test_it_searches_by_name_or_description(): void
    {
        $service = app(ProductService::class);

        Product::factory()->create(['name' => 'Bear', 'description' => 'hairy']);
        Product::factory()->create(['name' => 'Flower', 'description' => 'beatyfull']);

        $result = $service->list(['search'=>'Flower']);

        $this->assertCount(1,$result['products']);
        $this->assertEquals('Flower', $result['products']->first()->name);
    }
}
