<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'admin', 'description' => 'Administrator']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);
    }

    private function actingAsAdmin(): static
    {
        return $this->actingAs($this->adminUser, 'sanctum');
    }

    public function test_admin_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->actingAsAdmin()
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_admin_can_create_product(): void
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/products', [
                'name' => 'New Product',
                'sku' => 'NEW-001',
                'price' => 49.99,
                'stock_quantity' => 10,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'New Product', 'sku' => 'NEW-001']);

        $this->assertDatabaseHas('products', ['sku' => 'NEW-001']);
    }

    public function test_admin_can_view_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAsAdmin()
            ->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $product->id]);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAsAdmin()
            ->putJson("/api/products/{$product->id}", [
                'name' => 'New Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_admin_can_update_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $response = $this->actingAsAdmin()
            ->postJson("/api/products/{$product->id}/stock", [
                'quantity' => 5,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Stock updated successfully']);

        $product->refresh();
        $this->assertEquals(15, $product->stock_quantity);
    }

    public function test_unauthenticated_user_cannot_access_products(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401);
    }

    public function test_user_without_role_cannot_access_products(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/products');

        $response->assertStatus(403);
    }

    public function test_create_product_requires_name_sku_and_price(): void
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'sku', 'price']);
    }

    public function test_create_product_requires_unique_sku(): void
    {
        Product::factory()->create(['sku' => 'EXISTING-SKU']);

        $response = $this->actingAsAdmin()
            ->postJson('/api/products', [
                'name' => 'Another Product',
                'sku' => 'EXISTING-SKU',
                'price' => 10.00,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }
}
