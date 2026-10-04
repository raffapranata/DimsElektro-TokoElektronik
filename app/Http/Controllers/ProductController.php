<?php

namespace App\Http\Controllers;

use App\Enums\ProductSort;
use App\Http\Controllers\Concerns\CleansQueryString;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    use CleansQueryString;

    /**
     * Products shown per page in the catalog grid.
     */
    private const int PER_PAGE = 9;

    public function index(Request $request): View
    {
        // Filters are cleaned rather than validated: a malformed query string on
        // a public catalog should narrow the result, not return a 422 page.
        $search = $this->queryString($request, 'q');
        $categorySlug = $this->queryString($request, 'category', 120);
        $brand = $this->queryString($request, 'brand', 80);
        $sort = ProductSort::fromRequest($request->query('sort'));

        $products = Product::query()
            ->active()
            ->with('category:id,name,slug')
            ->search($search)
            ->inCategory($categorySlug)
            ->byBrand($brand);

        $sort->apply($products);

        return view('products.index', [
            'products' => $products->paginate(self::PER_PAGE)->withQueryString(),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'brands' => Product::availableBrands(),
            'activeCategory' => $categorySlug === null
                ? null
                : Category::query()->where('slug', $categorySlug)->first(),
            'sorts' => ProductSort::cases(),
            'filters' => [
                'q' => $search,
                'category' => $categorySlug,
                'brand' => $brand,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Product $product): View
    {
        // Inactive products must not be reachable through a public URL.
        abort_unless($product->is_active, 404);

        $product->load('category:id,name,slug');

        $relatedProducts = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->with('category:id,name,slug')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        return view('products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
