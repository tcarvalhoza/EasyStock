<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }

    public function test_can_register_user(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $user = $this->userService->register($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_can_find_user_by_id(): void
    {
        $user = User::factory()->create();

        $found = $this->userService->findById($user->id);

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_by_id_returns_null_for_nonexistent_user(): void
    {
        $found = $this->userService->findById(99999);

        $this->assertNull($found);
    }

    public function test_can_find_user_by_email(): void
    {
        $user = User::factory()->create(['email' => 'unique@example.com']);

        $found = $this->userService->findByEmail('unique@example.com');

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_can_assign_role_to_user(): void
    {
        $user = User::factory()->create();
        Role::create(['name' => 'admin', 'description' => 'Administrator']);

        $this->userService->assignRole($user, 'admin');

        $this->assertTrue($this->userService->hasRole($user, 'admin'));
    }

    public function test_has_role_returns_false_when_role_not_assigned(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->userService->hasRole($user, 'admin'));
    }

    public function test_register_user_with_role(): void
    {
        Role::create(['name' => 'cashier', 'description' => 'Cashier']);

        $data = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'role' => 'cashier',
        ];

        $user = $this->userService->register($data);

        $this->assertTrue($user->hasRole('cashier'));
    }
}
