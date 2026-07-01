<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_user(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'name', 'email']);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_register_requires_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user/me');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $user->id, 'email' => $user->email]);
    }

    public function test_admin_can_assign_role_to_user(): void
    {
        $adminRole = Role::create(['name' => 'admin', 'description' => 'Administrator']);
        Role::create(['name' => 'cashier', 'description' => 'Cashier']);

        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        $targetUser = User::factory()->create();

        $response = $this->actingAs($adminUser, 'sanctum')
            ->postJson("/api/users/{$targetUser->id}/role", [
                'role' => 'cashier',
            ]);

        $response->assertStatus(200);
        $this->assertTrue($targetUser->fresh()->hasRole('cashier'));
    }

    public function test_non_admin_cannot_assign_role(): void
    {
        Role::create(['name' => 'cashier', 'description' => 'Cashier']);

        $cashierRole = Role::where('name', 'cashier')->first();
        $cashierUser = User::factory()->create();
        $cashierUser->assignRole($cashierRole);

        $targetUser = User::factory()->create();

        $response = $this->actingAs($cashierUser, 'sanctum')
            ->postJson("/api/users/{$targetUser->id}/role", [
                'role' => 'cashier',
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/user/me');

        $response->assertStatus(401);
    }
}
