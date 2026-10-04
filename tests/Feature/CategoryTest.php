<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_view_the_category_list(): void
    {
        $categories = Category::factory()->count(3)->create();
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));

        $response->assertOk();
        foreach ($categories as $category) {
            $response->assertSee($category->name);
        }
    }

    public function test_guests_are_redirected_to_login_when_opening_the_category_list(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_a_customer_cannot_open_the_category_list(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_category(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Kamera & Fotografi',
            'description' => 'Kamera mirrorless, DSLR, dan aksesorisnya.',
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->where('name', 'Kamera & Fotografi')->firstOrFail();
        $this->assertSame('kamera-fotografi', $category->slug);
        $this->assertSame('Kamera mirrorless, DSLR, dan aksesorisnya.', $category->description);
    }

    public function test_the_category_slug_is_generated_from_the_name(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Perangkat Audio',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Perangkat Audio',
            'slug' => 'perangkat-audio',
        ]);
    }

    public function test_a_category_client_cannot_supply_a_custom_slug(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Monitor Gaming',
            'slug' => 'slug-jahat',
        ]);

        $this->assertDatabaseMissing('categories', ['slug' => 'slug-jahat']);
        $this->assertDatabaseHas('categories', ['slug' => 'monitor-gaming']);
    }

    public function test_category_names_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Smartphone']);
        $admin = User::factory()->admin()->create();

        $response = $this->from(route('admin.categories.create'))
            ->actingAs($admin)
            ->post(route('admin.categories.store'), ['name' => 'Smartphone']);

        $response->assertRedirect(route('admin.categories.create'));
        $response->assertSessionHasErrors('name');
        $this->assertSame(1, Category::query()->where('name', 'Smartphone')->count());
    }

    public function test_category_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->from(route('admin.categories.create'))
            ->actingAs($admin)
            ->post(route('admin.categories.store'), ['name' => '   ']);

        $response->assertRedirect(route('admin.categories.create'));
        $response->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Nama Lama']);

        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Nama Baru',
            'description' => 'Deskripsi baru.',
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertSame('Nama Baru', $category->name);
        $this->assertSame('nama-baru', $category->slug);
    }

    public function test_updating_a_category_keeps_its_name_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Kategori Lain']);
        $category = Category::factory()->create(['name' => 'Kategori Ini']);

        $response = $this->from(route('admin.categories.edit', $category))
            ->actingAs($admin)
            ->put(route('admin.categories.update', $category), ['name' => 'Kategori Lain']);

        $response->assertSessionHasErrors('name');
        $this->assertSame('Kategori Ini', $category->refresh()->name);
    }

    public function test_a_category_without_products_can_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_a_category_with_products_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->forCategory($category)->create();

        $response = $this->from(route('admin.categories.index'))
            ->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
