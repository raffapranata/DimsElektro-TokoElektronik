<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\CleansQueryString;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProductController extends Controller
{
    use CleansQueryString;

    /**
     * Products shown per page.
     */
    private const int PER_PAGE = 15;

    public function __construct(private readonly ProductImageService $images) {}

    public function index(Request $request): View
    {
        $search = $this->queryString($request, 'q');
        $categorySlug = $this->queryString($request, 'category', 120);
        $brand = $this->queryString($request, 'brand', 80);
        $stockState = $this->queryString($request, 'stock', 20);
        $activeState = $this->queryString($request, 'status', 20);

        $products = Product::query()
            ->with('category:id,name,slug')
            ->withCount('orderItems')
            ->search($search)
            ->inCategory($categorySlug)
            ->byBrand($brand)
            ->withStockState($stockState, (int) config('store.low_stock_threshold'))
            ->withActiveState($activeState)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $this->categoryOptions(),
            'brands' => Product::availableBrands(),
            'lowStockThreshold' => (int) config('store.low_stock_threshold'),
            'filters' => [
                'q' => $search,
                'category' => $categorySlug,
                'brand' => $brand,
                'stock' => $stockState,
                'status' => $activeState,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product([
                'is_active' => true,
                'stock' => 0,
                'warranty_months' => 12,
            ]),
            'categories' => $this->categoryOptions(),
            'brands' => Product::availableBrands(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = new Product($request->productAttributes());

        if ($request->hasFile('image')) {
            $product->image = $this->images->store($request->file('image'));
        }

        $product->save();

        return redirect()
            ->route('admin.products.index')
            ->with('status', sprintf('Produk "%s" berhasil ditambahkan.', $product->name));
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product->loadCount('orderItems'),
            'categories' => $this->categoryOptions(),
            'brands' => Product::availableBrands(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->fill($request->productAttributes());

        if ($request->hasFile('image')) {
            $product->image = $this->images->replace($product->image, $request->file('image'));
        } elseif ($request->boolean('remove_image')) {
            $this->images->delete($product->image);
            $product->image = null;
        }

        $product->save();

        return redirect()
            ->route('admin.products.index')
            ->with('status', sprintf('Produk "%s" berhasil diperbarui.', $product->name));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->loadCount('orderItems');

        // Products that appear in an order are never deleted: the order history
        // (and the product_name snapshot) must stay intact. They are deactivated.
        if ($product->order_items_count > 0) {
            $product->update(['is_active' => false]);

            return redirect()
                ->route('admin.products.index')
                ->with('status', sprintf(
                    'Produk "%s" dinonaktifkan karena memiliki %d riwayat pesanan. Produk tidak dihapus agar histori pesanan tetap utuh.',
                    $product->name,
                    $product->order_items_count,
                ));
        }

        $this->images->delete($product->image);
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', sprintf('Produk "%s" berhasil dihapus.', $product->name));
    }

    public function toggleActive(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('status', sprintf(
            'Produk "%s" %s.',
            $product->name,
            $product->is_active ? 'diaktifkan' : 'dinonaktifkan',
        ));
    }

    /**
     * @return Collection<int, Category>
     */
    private function categoryOptions(): Collection
    {
        return Category::query()->orderBy('name')->get(['id', 'name', 'slug']);
    }
}
