<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 */
class Role extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Retorna os usuários que possuem este papel.
     *
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
