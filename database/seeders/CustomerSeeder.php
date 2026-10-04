<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, email: string}>
     */
    private const CUSTOMERS = [
        ['name' => 'Budi Santoso', 'email' => 'budi@example.com'],
        ['name' => 'Siti Rahmawati', 'email' => 'siti@example.com'],
    ];

    public function run(): void
    {
        foreach (self::CUSTOMERS as $customer) {
            $user = User::query()->firstOrNew(['email' => $customer['email']]);

            $user->name = $customer['name'];
            $user->password = 'password';
            $user->email_verified_at = now();
            // Assigned directly: "role" is deliberately not mass assignable.
            $user->role = UserRole::Customer;
            $user->save();
        }
    }
}
