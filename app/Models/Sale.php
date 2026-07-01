<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $coupon_number
 * @property string $total_amount
 * @property string $discount_amount
 * @property string $final_amount
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SaleItem> $items
 */
class Sale extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coupon_number',
        'total_amount',
        'discount_amount',
        'final_amount',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    /**
     * Retorna o usuário que realizou a venda.
     *
     * @return BelongsTo<User, Sale>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna os itens da venda.
     *
     * @return HasMany<SaleItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Marca a venda como concluída e registra o horário de conclusão.
     *
     * @return void
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Marca a venda como cancelada.
     *
     * @return void
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
