<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'organization_id',
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
     * Get the organization that the user belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
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
     * Check if user needs organization (buyers only).
     */
    public function needsOrganization(): bool
    {
        return $this->isBuyer() && is_null($this->organization_id);
    }
}
