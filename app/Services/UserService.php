<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService implements UserServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (isset($data['role'])) {
            $this->assignRole($user, $data['role']);
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function assignRole(User $user, string $roleName): void
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $user->assignRole($role);
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole(User $user, string $roleName): bool
    {
        return $user->hasRole($roleName);
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email): ?User
    {
        return User::where(compact('email'))->first();
    }
}
