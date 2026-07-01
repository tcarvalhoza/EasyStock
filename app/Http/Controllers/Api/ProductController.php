<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductServiceInterface $productService
    ) {}

    /**
     * Lista produtos paginados com filtros opcionais (name, is_active, per_page).
     *
     * @param Request $request Filtros enviados na query string.
     * @return JsonResponse Paginador serializado com status 200.
     */
    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->getAll($request->all());

        return response()->json($products);
    }

    /**
     * Cria um novo produto.
     *
     * @param Request $request Dados do produto a ser criado.
     * @return JsonResponse Produto criado com status 201.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $product = $this->productService->create($validated);

        return response()->json($product, 201);
    }

    /**
     * Exibe os dados de um produto pelo ID.
     *
     * @param int $id ID do produto.
     * @return JsonResponse Produto encontrado, ou {message} com 404.
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->findById($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    /**
     * Atualiza parcialmente ou totalmente um produto.
     *
     * @param Request $request Dados a atualizar.
     * @param int $id ID do produto.
     * @return JsonResponse Produto atualizado, ou {message} com 404.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|unique:products,sku,' . $id,
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $product = $this->productService->update($id, $validated);

            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Remove (soft delete) um produto.
     *
     * @param int $id ID do produto.
     * @return JsonResponse 204 sem conteúdo, ou {message} com 404.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productService->delete($id);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Ajusta o estoque de um produto (positivo para entrada, negativo para saída).
     *
     * @param Request $request Dados com a quantidade a ajustar.
     * @param int $id ID do produto.
     * @return JsonResponse {message} com 200, ou {message} com 404.
     */
    public function updateStock(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer',
        ]);

        try {
            $this->productService->updateStock($id, $validated['quantity']);

            return response()->json(['message' => 'Stock updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
