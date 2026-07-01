<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    /**
     * Registra um novo usuário.
     *
     * @param Request $request Dados de registro (name, email, password, role opcional).
     * @return JsonResponse Usuário criado com status 201.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|string|exists:roles,name',
        ]);

        $user = $this->userService->register($validated);

        return response()->json($user, 201);
    }

    /**
     * Retorna os dados do usuário autenticado.
     *
     * @return JsonResponse Usuário autenticado com status 200.
     */
    public function me(): JsonResponse
    {
        $user = Auth::user();

        return response()->json($user);
    }

    /**
     * Atribui um papel a um usuário (apenas admin).
     *
     * @param Request $request Dados com o nome do papel.
     * @param int $id ID do usuário alvo.
     * @return JsonResponse Usuário com roles carregadas, ou {message} com 404.
     */
    public function assignRole(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = $this->userService->findById($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->userService->assignRole($user, $validated['role']);

        return response()->json($user->load('roles'));
    }
}
