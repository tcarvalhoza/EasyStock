<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    private User $cashierUser;
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'admin', 'description' => 'Administrator']);
        $cashierRole = Role::create(['name' => 'cashier', 'description' => 'Cashier']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);

        $this->cashierUser = User::factory()->create();
        $this->cashierUser->assignRole($cashierRole);
    }

    public function test_cashier_can_create_sale(): void
    {
        $product = Product::factory()->create([
            'price' => 25.00,
            'stock_quantity' => 20,
        ]);

        $response = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/sales', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'coupon_number', 'total_amount', 'status', 'items']);

        $response->assertJsonFragment(['status' => 'pending']);
        $this->assertEquals('50.00', $response->json('total_amount'));

        $product->refresh();
        $this->assertEquals(18, $product->stock_quantity);
    }

    public function test_cannot_create_sale_with_insufficient_stock(): void
    {
        $product = Product::factory()->create([
            'price' => 10.00,
            'stock_quantity' => 1,
        ]);

        $response = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/sales', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 5],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);
    }

    public function test_admin_can_complete_sale(): void
    {
        $product = Product::factory()->create(['price' => 10.00, 'stock_quantity' => 20]);

        $sale = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/sales', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ])
            ->json();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/sales/{$sale['id']}/complete");

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'completed']);
    }

    public function test_admin_can_cancel_sale_and_stock_is_restored(): void
    {
        $product = Product::factory()->create(['price' => 10.00, 'stock_quantity' => 10]);

        $sale = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/sales', [
                'items' => [['product_id' => $product->id, 'quantity' => 3]],
            ])
            ->json();

        $this->actingAs($this->adminUser, 'sanctum')
            ->postJson("/api/sales/{$sale['id']}/cancel")
            ->assertStatus(200)
            ->assertJsonFragment(['status' => 'cancelled']);

        $product->refresh();
        $this->assertEquals(10, $product->stock_quantity);
    }

    public function test_can_view_sale(): void
    {
        $product = Product::factory()->create(['price' => 10.00, 'stock_quantity' => 10]);

        $sale = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/sales', [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ])
            ->json();

        $response = $this->actingAs($this->cashierUser, 'sanctum')
            ->getJson("/api/sales/{$sale['id']}");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'coupon_number', 'items']);
    }

    public function test_can_generate_fiscal_coupon(): void
    {
        $product = Product::factory()->create(['name' => 'Test Item', 'price' => 15.00, 'stock_quantity' => 10]);

        $sale = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/sales', [
                'items' => [['product_id' => $product->id, 'quantity' => 2]],
            ])
            ->json();

        $response = $this->actingAs($this->cashierUser, 'sanctum')
            ->getJson("/api/sales/{$sale['id']}/coupon");

        $response->assertStatus(200)
            ->assertJsonStructure(['coupon']);

        $coupon = $response->json('coupon');
        $this->assertStringContainsString('FISCAL COUPON', $coupon);
        $this->assertStringContainsString('Test Item', $coupon);
        $this->assertStringContainsString('30.00', $coupon);
    }

    public function test_unauthenticated_user_cannot_create_sale(): void
    {
        $response = $this->postJson('/api/sales', []);

        $response->assertStatus(401);
    }

    public function test_sale_requires_items(): void
    {
        $response = $this->actingAs($this->cashierUser, 'sanctum')
            ->postJson('/api/sales', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }
}
