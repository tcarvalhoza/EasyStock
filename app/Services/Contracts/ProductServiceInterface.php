<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    /**
     * Cria um novo produto no sistema.
     *
     * @param array{name: string, sku: string, description?: string, price: float, stock_quantity?: int, is_active?: bool} $data Dados do produto.
     * @return Product Produto criado.
     */
    public function create(array $data): Product;

    /**
     * Atualiza os dados de um produto existente.
     *
     * @param int $id ID do produto.
     * @param array $data Campos a atualizar.
     * @return Product Produto atualizado.
     *
     * @throws \Exception Se o produto não for encontrado.
     */
    public function update(int $id, array $data): Product;

    /**
     * Remove (soft delete) um produto pelo ID.
     *
     * @param int $id ID do produto.
     * @return void
     *
     * @throws \Exception Se o produto não for encontrado.
     */
    public function delete(int $id): void;

    /**
     * Busca um produto pelo ID.
     *
     * @param int $id ID do produto.
     * @return Product|null Produto encontrado ou null.
     */
    public function findById(int $id): ?Product;

    /**
     * Busca um produto pelo SKU.
     *
     * @param string $sku Código SKU do produto.
     * @return Product|null Produto encontrado ou null.
     */
    public function findBySku(string $sku): ?Product;

    /**
     * Retorna a listagem paginada de produtos com filtros opcionais.
     *
     * @param array{name?: string, is_active?: bool, per_page?: int} $filters Filtros de busca.
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = []): LengthAwarePaginator;

    /**
     * Incrementa ou decrementa o estoque de um produto.
     *
     * Valores positivos aumentam o estoque; negativos diminuem.
     *
     * @param int $productId ID do produto.
     * @param int $quantity Quantidade a ajustar (positivo ou negativo).
     * @return void
     *
     * @throws \Exception Se o produto não for encontrado.
     */
    public function updateStock(int $productId, int $quantity): void;

    /**
     * Verifica se há estoque suficiente para a quantidade solicitada.
     *
     * @param int $productId ID do produto.
     * @param int $quantity Quantidade desejada.
     * @return bool True se há estoque disponível.
     */
    public function checkStockAvailability(int $productId, int $quantity): bool;
}
