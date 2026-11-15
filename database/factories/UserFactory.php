<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Role;
use App\Models\ManajemenUser\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement([Role::Staff, Role::Intern]),
            'is_active' => true,
            'is_trusted' => false,
            'remember_token' => Str::random(10),
        ];
    }
}
