<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telegram_id',
        'username',
        'first_name',
        'last_name',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Рестораны, созданные закупщиком
     */
    public function ownedRestaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class, 'created_by');
    }

    /**
     * Рестораны, где пользователь работает сотрудником (многие-ко-многим)
     */
    public function restaurants(): BelongsToMany
    {
        return $this->belongsToMany(Restaurant::class, 'restaurant_employees')
            ->withTimestamps();
    }

    /**
     * Get the orders created by this user (as buyer).
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * Get the order items added by this user (as employee).
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'employee_id');
    }

    /**
     * Check if user is a buyer (B2B).
     */
    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    /**
     * Check if user is an employee (B2B).
     */
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * Check if user is a regular customer (B2C).
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Check if user needs restaurant (buyers/employees only).
     */
    public function needsRestaurant(): bool
    {
        if ($this->isBuyer()) {
            return $this->ownedRestaurants()->count() === 0;
        }
        
        if ($this->isEmployee()) {
            return $this->restaurants()->count() === 0;
        }
        
        return false;
    }

    /**
     * Get all restaurants user has access to (owned + employed)
     */
    public function allRestaurants()
    {
        if ($this->isBuyer()) {
            return $this->ownedRestaurants;
        }
        
        if ($this->isEmployee()) {
            return $this->restaurants;
        }
        
        return collect();
    }
}
