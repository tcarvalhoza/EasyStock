<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function login(string $email, string $password): string
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return $user->createToken('api-token')->plainTextToken;
    }

    /**
     * {@inheritdoc}
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
