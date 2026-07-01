<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService();
    }

    public function test_can_create_product(): void
    {
        $data = [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'description' => 'Test description',
            'price' => 99.99,
            'stock_quantity' => 10,
        ];

        $product = $this->productService->create($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('TEST-001', $product->sku);
        $this->assertEquals(99.99, $product->price);
        $this->assertEquals(10, $product->stock_quantity);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 50.00,
        ]);

        $updated = $this->productService->update($product->id, [
            'name' => 'Updated Name',
            'price' => 75.00,
        ]);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals(75.00, $updated->price);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->productService->delete($product->id);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_can_find_product_by_id(): void
    {
        $product = Product::factory()->create();

        $found = $this->productService->findById($product->id);

        $this->assertEquals($product->id, $found->id);
    }

    public function test_can_find_product_by_sku(): void
    {
        $product = Product::factory()->create(['sku' => 'UNIQUE-SKU']);

        $found = $this->productService->findBySku('UNIQUE-SKU');

        $this->assertEquals($product->id, $found->id);
    }

    public function test_check_stock_availability(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->assertTrue($this->productService->checkStockAvailability($product->id, 5));
        $this->assertFalse($this->productService->checkStockAvailability($product->id, 15));
    }

    public function test_can_update_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->productService->updateStock($product->id, 5);

        $product->refresh();
        $this->assertEquals(15, $product->stock_quantity);
    }
}
