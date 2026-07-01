<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\User;

interface UserServiceInterface
{
    /**
     * Registra um novo usuário no sistema.
     *
     * @param array{name: string, email: string, password: string, role?: string} $data Dados do usuário.
     * @return User Usuário criado.
     */
    public function register(array $data): User;

    /**
     * Atribui um papel (role) ao usuário.
     *
     * @param User $user Usuário alvo.
     * @param string $roleName Nome do papel (ex: 'admin', 'manager', 'cashier').
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Se o papel não existir.
     */
    public function assignRole(User $user, string $roleName): void;

    /**
     * Verifica se o usuário possui determinado papel.
     *
     * @param User $user Usuário a verificar.
     * @param string $roleName Nome do papel.
     * @return bool
     */
    public function hasRole(User $user, string $roleName): bool;

    /**
     * Busca um usuário pelo ID.
     *
     * @param int $id ID do usuário.
     * @return User|null Usuário encontrado ou null.
     */
    public function findById(int $id): ?User;

    /**
     * Busca um usuário pelo e-mail.
     *
     * @param string $email E-mail do usuário.
     * @return User|null Usuário encontrado ou null.
     */
    public function findByEmail(string $email): ?User;
}
