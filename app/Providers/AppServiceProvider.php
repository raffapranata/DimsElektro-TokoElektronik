<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Surface N+1 queries and silently discarded attributes outside
        // production instead of hiding them until they become performance bugs.
        Model::shouldBeStrict(! $this->app->isProduction());

        $this->composeStoreLayout();
    }

    /**
     * The storefront header needs the category menu and the cart badge on
     * every page, so it is prepared once here instead of inside the layout.
     */
    private function composeStoreLayout(): void
    {
        View::composer('layouts.app', function (ViewInstance $view): void {
            $view->with([
                'navCategories' => Category::query()
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']),
                'cartItemCount' => $this->app->make(CartService::class)->totalQuantity(),
            ]);
        });
    }
}
