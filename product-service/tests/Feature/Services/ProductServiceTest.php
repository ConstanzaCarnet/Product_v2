<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Services\ProductService;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;


class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_product()
    {
        $service = app(ProductService::class);

        $data = [
            'name' => 'Producto Test',
            'description' => 'Descripción test',
            'price' => 1000,
            'stock' => 5,
        ];

        $product = $service->store($data);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Producto Test',
        ]);
    }

    //test para testear regla de negocios
    public function test_product_is_active_by_default()
    {
        $service = app(ProductService::class);

        $product = $service->store([
            'name' => 'Producto Activo',
            'description' => 'Test',
            'price' => 200,
            'stock' => 1,
        ]);

        $this->assertTrue($product->active);
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
    //testeamos que no elimine si el stock es mayor a 0
    public function test_cannot_delete_product_with_stock()
    {
        $product = Product::factory()->create([
            'stock' => 10,
        ]);

        $service = app(ProductService::class);

        $this->expectException(ValidationException::class);

        $service->delete($product);
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

        $this->assertCount(2, $result['products']);
        $this->assertEquals([100, 200], $result['products']->pluck('price')->values()->all());
    }
    //looking for name
    public function test_it_searches_by_name_or_description(): void
    {
        $service = app(ProductService::class);

        Product::factory()->create(['name' => 'Bear', 'description' => 'hairy']);
        Product::factory()->create(['name' => 'Flower', 'description' => 'beatyfull']);

        $result = $service->list(['search'=>'Flower']);

        $this->assertEquals('Flower', $result['products']->first()->name);
    }
}
