<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\CustomerSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_screen_renders(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke akun Anda');
    }

    public function test_register_screen_renders(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Buat akun pelanggan');
    }

    public function test_customer_can_login_with_valid_credentials(): void
    {
        $customer = User::factory()->customer()->create(['password' => 'rahasia123']);

        $response = $this->post('/login', [
            'email' => $customer->email,
            'password' => 'rahasia123',
        ]);

        $this->assertAuthenticatedAs($customer);
        $response->assertRedirect(route('home'));
    }

    public function test_admin_is_redirected_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'rahasia123']);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'rahasia123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_login_fails_with_an_incorrect_password(): void
    {
        $user = User::factory()->customer()->create(['password' => 'rahasia123']);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'salah-total',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_login_is_throttled_after_five_failed_attempts(): void
    {
        $user = User::factory()->customer()->create(['password' => 'rahasia123']);

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['email' => $user->email, 'password' => 'salah-total']);
        }

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Terlalu banyak percobaan masuk',
            session('errors')->first('email'),
        );
    }

    public function test_customer_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Rina Wulandari',
            'email' => 'rina@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $user = User::query()->where('email', 'rina@example.com')->firstOrFail();

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertDatabaseHas('users', [
            'email' => 'rina@example.com',
            'role' => UserRole::Customer->value,
        ]);
    }

    public function test_registration_ignores_a_role_supplied_by_the_client(): void
    {
        $this->post('/register', [
            'name' => 'Penyusup Akun',
            'email' => 'penyusup@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => UserRole::Admin->value,
        ]);

        $user = User::query()->where('email', 'penyusup@example.com')->firstOrFail();

        $this->assertSame(UserRole::Customer, $user->role);
    }

    public function test_registration_requires_a_unique_email(): void
    {
        User::factory()->create(['email' => 'terdaftar@example.com']);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Nama Baru',
            'email' => 'terdaftar@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_requires_a_matching_password_confirmation(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'beda-sekali',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('home'));
    }

    public function test_guests_cannot_reach_the_logout_route(): void
    {
        $this->post('/logout')->assertRedirect(route('login'));
    }

    public function test_seeded_admin_can_login(): void
    {
        // Run seeder and verify admin can login with password 'password'
        $this->seed(AdminUserSeeder::class);

        $this->post('/login', [
            'email' => 'admin@dimselektro.test',
            'password' => 'password',
        ])
            ->assertRedirect(route('admin.dashboard'));

        $admin = User::where('email', 'admin@dimselektro.test')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->isAdmin());
        $this->assertAuthenticatedAs($admin);
    }

    public function test_seeded_customers_can_login(): void
    {
        // Run seeder and verify customers can login with password 'password'
        $this->seed(CustomerSeeder::class);

        foreach (['budi@example.com', 'siti@example.com'] as $email) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'password',
            ])
                ->assertRedirect(route('home'));

            $customer = User::where('email', $email)->first();
            $this->assertNotNull($customer);
            $this->assertTrue($customer->isCustomer());
            $this->assertAuthenticatedAs($customer);

            $this->post('/logout');
        }
    }
}
