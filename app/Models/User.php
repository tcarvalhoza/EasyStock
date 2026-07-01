<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role> $roles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sale> $sales
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Retorna os papéis (roles) associados ao usuário.
     *
     * @return BelongsToMany<Role>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Verifica se o usuário possui o papel informado.
     *
     * @param  string $roleName Nome do papel.
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Associa um papel ao usuário (sem remover os existentes).
     *
     * @param  Role $role Papel a ser atribuído.
     * @return void
     */
    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching($role);
    }

    /**
     * Retorna as vendas realizadas pelo usuário.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Sale>
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
