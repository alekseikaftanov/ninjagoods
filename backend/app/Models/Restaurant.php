<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'legal_name',
        'inn',
        'kpp',
        'ogrn',
        'address_legal',
        'address_actual',
        'phone',
        'created_by',
    ];

    /**
     * Закупщик, создавший ресторан
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Сотрудники ресторана (многие-ко-многим)
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_employees')
            ->withTimestamps();
    }

    /**
     * Заказы ресторана
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Инвайты для ресторана
     */
    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class);
    }

    /**
     * Проверка, является ли пользователь владельцем ресторана
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    /**
     * Проверка, является ли пользователь сотрудником ресторана
     */
    public function hasEmployee(User $user): bool
    {
        return $this->employees()->where('users.id', $user->id)->exists();
    }

    /**
     * Добавить сотрудника в ресторан
     */
    public function addEmployee(User $user): void
    {
        if (!$this->hasEmployee($user)) {
            $this->employees()->attach($user->id);
        }
    }

    /**
     * Удалить сотрудника из ресторана
     */
    public function removeEmployee(User $user): void
    {
        $this->employees()->detach($user->id);
    }
}
