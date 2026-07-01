<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private SaleService $saleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->saleService = new SaleService();
    }

    public function test_can_create_sale(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 10.00,
            'stock_quantity' => 20,
        ]);

        $items = [
            ['product_id' => $product->id, 'quantity' => 2],
        ];

        $sale = $this->saleService->createSale($items, $user->id);

        $this->assertEquals(20.00, $sale->total_amount);
        $this->assertEquals('pending', $sale->status);
        $this->assertCount(1, $sale->items);

        $product->refresh();
        $this->assertEquals(18, $product->stock_quantity);
    }

    public function test_cannot_create_sale_with_insufficient_stock(): void
    {
        $this->expectException(\Exception::class);

        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 10.00,
            'stock_quantity' => 5,
        ]);

        $items = [
            ['product_id' => $product->id, 'quantity' => 10],
        ];

        $this->saleService->createSale($items, $user->id);
    }

    public function test_can_complete_sale(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10.00, 'stock_quantity' => 20]);

        $items = [['product_id' => $product->id, 'quantity' => 2]];
        $sale = $this->saleService->createSale($items, $user->id);

        $completed = $this->saleService->completeSale($sale->id);

        $this->assertEquals('completed', $completed->status);
        $this->assertNotNull($completed->completed_at);
    }

    public function test_can_cancel_sale_and_restore_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10.00, 'stock_quantity' => 20]);

        $items = [['product_id' => $product->id, 'quantity' => 2]];
        $sale = $this->saleService->createSale($items, $user->id);

        $cancelled = $this->saleService->cancelSale($sale->id);

        $this->assertEquals('cancelled', $cancelled->status);

        $product->refresh();
        $this->assertEquals(20, $product->stock_quantity);
    }

    public function test_generate_fiscal_coupon(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Test Product', 'price' =>  10.00]);

        $items = [['product_id' => $product->id, 'quantity' => 2]];
        $sale = $this->saleService->createSale($items, $user->id);

        $coupon = $this->saleService->generateFiscalCoupon($sale);

        $this->assertStringContainsString('FISCAL COUPON', $coupon);
        $this->assertStringContainsString('Test Product', $coupon);
        $this->assertStringContainsString('20.00', $coupon);
    }
}
