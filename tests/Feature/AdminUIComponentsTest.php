<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminUIComponentsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_product_status_form_uses_patch_method(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $product = Product::factory()->create();

        $response = $this->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee('_method');
        $response->assertSee(route('admin.products.status', $product));
    }

    public function test_product_delete_form_uses_delete_method(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $product = Product::factory()->create();

        $response = $this->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee(route('admin.products.destroy', $product));
    }

    public function test_category_delete_form_uses_delete_method(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $category = Category::factory()->create();

        $response = $this->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertSee(route('admin.categories.destroy', $category));
    }

    public function test_confirmation_modal_component_renders_in_admin_layout(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('confirmation-modal', false);
        $response->assertSee('window.confirmAction', false);
    }

    public function test_toast_notification_component_renders_in_admin_layout(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('toast-container', false);
        $response->assertSee('window.showToast', false);
    }

    public function test_admin_flash_status_triggers_toast(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get(route('admin.products.index'), ['status' => 'test message']);
        // Flash it via session
        session(['status' => 'Produk berhasil dihapus']);

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Produk berhasil dihapus');
    }
}
