<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Autentica o usuário e retorna um token de acesso Sanctum.
     *
     * @param string $email E-mail do usuário.
     * @param string $password Senha em texto plano.
     * @return string Token Bearer gerado.
     *
     * @throws \Illuminate\Auth\AuthenticationException Se as credenciais forem inválidas.
     */
    public function login(string $email, string $password): string;

    /**
     * Invalida o token de acesso atual do usuário.
     *
     * @param User $user Usuário autenticado.
     * @return void
     */
    public function logout(User $user): void;
}
