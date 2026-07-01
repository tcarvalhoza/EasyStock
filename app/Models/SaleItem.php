<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $sale_id
 * @property int $product_id
 * @property int $quantity
 * @property string $unit_price
 * @property string $subtotal
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Sale $sale
 * @property-read Product $product
 */
class SaleItem extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Retorna a venda à qual o item pertence.
     *
     * @return BelongsTo<Sale, SaleItem>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Retorna o produto associado ao item de venda.
     *
     * @return BelongsTo<Product, SaleItem>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
