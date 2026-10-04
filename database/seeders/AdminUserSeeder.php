<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Development administrator. The password is intentionally trivial: this
     * seeder is for local development only and is never run in production.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrNew(['email' => 'admin@dimselektro.test']);

        $admin->name = 'Andi Pratama';
        $admin->password = 'password';
        $admin->email_verified_at = now();
        // Assigned directly: "role" is deliberately not mass assignable.
        $admin->role = UserRole::Admin;
        $admin->save();
    }
}
