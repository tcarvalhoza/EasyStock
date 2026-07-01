<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * Autentica o usuário e retorna um token Bearer.
     *
     * @param Request $request Dados de autenticação (email e password).
     * @return JsonResponse JSON com {token} em caso de sucesso ou {message} com 401.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        try {
            $token = $this->authService->login($validated['email'], $validated['password']);

            return response()->json(['token' => $token]);
        } catch (AuthenticationException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    /**
     * Invalida o token do usuário autenticado.
     *
     * @param Request $request Requisição autenticada.
     * @return JsonResponse JSON com {message} com status 200.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out successfully']);
    }
}
