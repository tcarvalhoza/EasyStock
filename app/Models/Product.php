<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $sku
 * @property string|null $description
 * @property string $price
 * @property int $stock_quantity
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SaleItem> $saleItems
 */
class Product extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
        'stock_quantity',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Retorna os itens de venda associados ao produto.
     *
     * @return HasMany<SaleItem>
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Verifica se há estoque disponível para a quantidade solicitada.
     *
     * @param  int  $quantity Quantidade desejada.
     * @return bool
     */
    public function isInStock(int $quantity): bool
    {
        return $this->stock_quantity >= $quantity;
    }

    /**
     * Decrementa o estoque do produto.
     *
     * @param  int  $quantity Quantidade a subtrair.
     * @return void
     */
    public function decreaseStock(int $quantity): void
    {
        $this->decrement('stock_quantity', $quantity);
    }

    /**
     * Incrementa o estoque do produto.
     *
     * @param  int  $quantity Quantidade a adicionar.
     * @return void
     */
    public function increaseStock(int $quantity): void
    {
        $this->increment('stock_quantity', $quantity);
    }
}
