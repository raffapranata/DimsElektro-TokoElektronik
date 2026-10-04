<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\CleansQueryString;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use CleansQueryString;

    /**
     * Categories shown per page.
     */
    private const int PER_PAGE = 15;

    public function index(Request $request): View
    {
        $search = $this->queryString($request, 'q');

        $categories = Category::query()
            ->search($search)
            ->withCount('products')
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', ['category' => new Category]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', sprintf('Kategori "%s" berhasil ditambahkan.', $category->name));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category->loadCount('products'),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', sprintf('Kategori "%s" berhasil diperbarui.', $category->name));
    }

    public function destroy(Category $category): RedirectResponse
    {
        // A category that still owns products is never hard deleted: the foreign
        // key is RESTRICT, and the admin gets an actionable message instead.
        $category->loadCount('products');

        if ($category->products_count > 0) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', sprintf(
                    'Kategori "%s" masih memiliki %d produk. Pindahkan atau hapus produk tersebut terlebih dahulu.',
                    $category->name,
                    $category->products_count,
                ));
        }

        try {
            $category->delete();
        } catch (QueryException) {
            // Covers the race where a product is created between the check above
            // and the delete statement.
            return redirect()
                ->route('admin.categories.index')
                ->with('error', sprintf('Kategori "%s" tidak dapat dihapus karena masih terhubung dengan data lain.', $category->name));
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('status', sprintf('Kategori "%s" berhasil dihapus.', $category->name));
    }
}
