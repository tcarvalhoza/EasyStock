<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\SaleServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    public function __construct(
        private readonly SaleServiceInterface $saleService
    ) {}

    /**
     * Cria uma nova venda com os itens informados.
     *
     * @param Request $request Itens da venda (product_id e quantity).
     * @return JsonResponse Venda criada com itens e produtos, status 201; ou {message} com 422 se sem estoque.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $sale = $this->saleService->createSale(
                $validated['items'],
                Auth::id()
            );

            return response()->json($sale, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Exibe os dados de uma venda pelo ID.
     *
     * @param int $id ID da venda.
     * @return JsonResponse Venda com itens, ou {message} com 404.
     */
    public function show(int $id): JsonResponse
    {
        $sale = $this->saleService->findById($id);

        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }

        return response()->json($sale);
    }

    /**
     * Conclui uma venda pendente.
     *
     * @param int $id ID da venda.
     * @return JsonResponse Venda atualizada, ou {message} com 404.
     */
    public function complete(int $id): JsonResponse
    {
        try {
            $sale = $this->saleService->completeSale($id);

            return response()->json($sale);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Cancela uma venda e restaura o estoque dos produtos.
     *
     * @param int $id ID da venda.
     * @return JsonResponse Venda cancelada, ou {message} com 404.
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $sale = $this->saleService->cancelSale($id);

            return response()->json($sale);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Gera e retorna o cupom fiscal de uma venda.
     *
     * @param int $id ID da venda.
     * @return JsonResponse {coupon: string} com 200, ou {message} com 404.
     */
    public function coupon(int $id): JsonResponse
    {
        $sale = $this->saleService->findById($id);

        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }

        $coupon = $this->saleService->generateFiscalCoupon($sale);

        return response()->json(['coupon' => $coupon]);
    }
}
