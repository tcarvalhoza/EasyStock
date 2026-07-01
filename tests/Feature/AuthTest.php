<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
