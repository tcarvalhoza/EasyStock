<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Sale;

interface SaleServiceInterface
{
    /**
     * Cria uma nova venda com os itens informados.
     *
     * Valida disponibilidade de estoque e decrementa as quantidades
     * de cada produto dentro de uma transação atômica.
     *
     * @param array<int, array{product_id: int, quantity: int}> $items Itens da venda.
     * @param int $userId ID do usuário operador.
     * @return Sale Venda criada com itens e produtos carregados.
     *
     * @throws \Exception Se algum produto estiver sem estoque suficiente.
     */
    public function createSale(array $items, int $userId): Sale;

    /**
     * Marca uma venda como concluída.
     *
     * @param int $saleId ID da venda.
     * @return Sale Venda atualizada.
     *
     * @throws \Exception Se a venda não for encontrada.
     */
    public function completeSale(int $saleId): Sale;

    /**
     * Cancela uma venda e restaura o estoque dos produtos.
     *
     * @param int $saleId ID da venda.
     * @return Sale Venda cancelada.
     *
     * @throws \Exception Se a venda não for encontrada.
     */
    public function cancelSale(int $saleId): Sale;

    /**
     * Busca uma venda pelo ID com itens e produtos carregados.
     *
     * @param int $id ID da venda.
     * @return Sale|null Venda encontrada ou null.
     */
    public function findById(int $id): ?Sale;

    /**
     * Gera o texto do cupom fiscal de uma venda.
     *
     * @param Sale $sale Venda com itens e produtos já carregados.
     * @return string Cupom fiscal formatado em texto.
     */
    public function generateFiscalCoupon(Sale $sale): string;
}
