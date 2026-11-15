<?php

declare(strict_types=1);

namespace App\Models\ManajemenUser;

use App\Enums\Role;
use App\Models\Tugas\Tugas;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'is_active',
        'is_trusted',
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
            'role' => Role::class,
            'is_active' => 'boolean',
            'is_trusted' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessor: Staff selalu dianggap trusted.
     */
    protected function isTrusted(): Attribute
    {
        return Attribute::make(
            get: fn (?bool $value, array $attributes): bool => 
                isset($attributes['role']) && $attributes['role'] === Role::Staff->value
                    ? true
                    : (bool) ($value ?? false),
        );
    }

    public function tugasSebagaiPic(): HasMany
    {
        return $this->hasMany(Tugas::class, 'pic_id');
    }

    public function tugasSebagaiPemegang(): HasMany
    {
        return $this->hasMany(Tugas::class, 'pemegang_id');
    }
}
