<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ProductService implements ProductServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(array $data): Product
    {
        return Product::create([
            'name' => $data['name'],
            'sku' => $data['sku'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): Product
    {
        $product = $this->findById($id);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        $product->update($data);

        return $product->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): void
    {
        $product = $this->findById($id);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        $product->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Product::query();

        if (isset($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStock(int $productId, int $quantity): void
    {
        $product = $this->findById($productId);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        if ($quantity > 0) {
            $product->increaseStock($quantity);
        } else {
            $product->decreaseStock(abs($quantity));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkStockAvailability(int $productId, int $quantity): bool
    {
        $product = $this->findById($productId);

        if (!$product) {
            return false;
        }

        return $product->isInStock($quantity);
    }
}
