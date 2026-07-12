<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\Contracts\SaleServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService implements SaleServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function createSale(array $items, int $userId): Sale
    {
        return DB::transaction(function () use ($items, $userId) {
            $totalAmount = 0;
            $saleItems = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if (!$product->isInStock($item['quantity'])) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $saleItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $sale = Sale::create([
                'user_id' => $userId,
                'coupon_number' => $this->generateCouponNumber(),
                'total_amount' => $totalAmount,
                'discount_amount' => 0,
                'final_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            foreach ($saleItems as $saleItem) {
                $sale->items()->create($saleItem);
                $product = Product::find($saleItem['product_id']);
                $product->decreaseStock($saleItem['quantity']);
            }

            return $sale->load('items.product');
        });
    }

    /**
     * {@inheritdoc}
     */
    public function completeSale(int $saleId): Sale
    {
        $sale = $this->findById($saleId);

        if (!$sale) {
            throw new \Exception('Sale not found');
        }

        $sale->markAsCompleted();

        return $sale->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function cancelSale(int $saleId): Sale
    {
        $sale = $this->findById($saleId);

        if (!$sale) {
            throw new \Exception('Sale not found');
        }

        return DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                $product = $item->product;
                $product->increaseStock($item->quantity);
            }

            $sale->markAsCancelled();

            return $sale->fresh();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int $id): ?Sale
    {
        return Sale::with('items.product')->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function listSales(array $filters = []): LengthAwarePaginator
    {
        $query = Sale::with(['items.product', 'user'])->orderBy('created_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        $perPage = !empty($filters['per_page']) ? (int) $filters['per_page'] : 15;

        return $query->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFiscalCoupon(Sale $sale): string
    {
        $coupon = "=== FISCAL COUPON ===\n";
        $coupon .= "Coupon Number: {$sale->coupon_number}\n";
        $coupon .= "Date: {$sale->created_at->format('Y-m-d H:i:s')}\n";
        $coupon .= "Status: {$sale->status}\n";
        $coupon .= "----------------------\n";

        foreach ($sale->items as $item) {
            $coupon .= "{$item->product->name}\n";
            $coupon .= "  Qty: {$item->quantity} x $" . number_format((float) $item->unit_price, 2) . "\n";
            $coupon .= "  Subtotal: $" . number_format((float) $item->subtotal, 2) . "\n";
        }

        $coupon .= "----------------------\n";
        $coupon .= "Total: $" . number_format((float) $sale->total_amount, 2) . "\n";
        $coupon .= "Discount: $" . number_format((float) $sale->discount_amount, 2) . "\n";
        $coupon .= "Final Amount: $" . number_format((float) $sale->final_amount, 2) . "\n";
        $coupon .= "======================\n";

        return $coupon;
    }

    /**
     * Gera um número de cupão único baseado na data/hora atual e um número aleatório.
     *
     * @return string Número de cupão no formato CUP-YmdHis-NNNN.
     */
    private function generateCouponNumber(): string
    {
        return 'CUP-' . date('YmdHis') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
