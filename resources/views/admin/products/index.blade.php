@extends('layouts.admin')

@section('title', 'Produk')
@section('heading', 'Produk')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-wrap items-center gap-2">
            <label for="q" class="sr-only">Cari produk</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Cari nama atau merek"
                   class="input w-52">

            <label for="filter-category" class="sr-only">Kategori</label>
            <select id="filter-category" name="category" class="input w-44">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <label for="filter-brand" class="sr-only">Merek</label>
            <select id="filter-brand" name="brand" class="input w-40">
                <option value="">Semua merek</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand }}" @selected($filters['brand'] === $brand)>{{ $brand }}</option>
                @endforeach
            </select>

            <label for="filter-stock" class="sr-only">Stok</label>
            <select id="filter-stock" name="stock" class="input w-36">
                <option value="">Semua stok</option>
                <option value="available" @selected($filters['stock'] === 'available')>Stok tersedia</option>
                <option value="low" @selected($filters['stock'] === 'low')>Stok menipis</option>
                <option value="out" @selected($filters['stock'] === 'out')>Stok habis</option>
            </select>

            <label for="filter-status" class="sr-only">Status</label>
            <select id="filter-status" name="status" class="input w-36">
                <option value="">Semua status</option>
                <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
            </select>

            <button type="submit" class="btn font-pixel text-[9px] uppercase">Filter</button>

            @if (array_filter($filters))
                <a href="{{ route('admin.products.index') }}" class="btn">Reset</a>
            @endif
        </form>

        <a href="{{ route('admin.products.create') }}" class="btn btn-primary font-pixel text-[9px] uppercase">
            + Tambah
        </a>
    </div>

    @if ($products->isEmpty())
        <x-empty-state
            class="mt-4"
            title="{{ array_filter($filters) ? 'Produk tidak ditemukan' : 'Belum ada produk' }}"
            description="{{ array_filter($filters) ? 'Coba ubah kata kunci atau filter.' : 'Tambahkan produk pertama untuk mulai berjualan.' }}">
            <x-slot:action>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Tambah produk</a>
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="card mt-4 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y-2 divide-zinc-900">
                    <thead class="bg-amber-50">
                        <tr>
                            <th scope="col" class="table-th">Produk</th>
                            <th scope="col" class="table-th">Kategori</th>
                            <th scope="col" class="table-th text-right">Harga</th>
                            <th scope="col" class="table-th text-center">Stok</th>
                            <th scope="col" class="table-th text-center">Status</th>
                            <th scope="col" class="table-th text-right">Terjual</th>
                            <th scope="col" class="table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-zinc-100">
                        @foreach ($products as $product)
                            <tr class="table-row-hover">
                                <td class="table-td">
                                    <div class="flex items-center gap-3">
                                        <x-product-thumb :product="$product" size="size-10" />
                                        <div class="min-w-0">
                                            <p class="font-bold text-zinc-900">
                                                <a href="{{ route('admin.products.edit', $product) }}" class="underline decoration-2 underline-offset-2 transition-colors hover:text-brand-700">
                                                    {{ $product->name }}
                                                </a>
                                            </p>
                                            <p class="text-xs font-medium text-zinc-500">{{ $product->brand }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-td text-zinc-600">{{ $product->category?->name ?? '-' }}</td>
                                <td class="table-td text-right font-bold text-zinc-900">
                                    <x-money :amount="$product->price" />
                                </td>
                                <td class="table-td text-center">
                                    @if ($product->stock <= 0)
                                        <span class="badge bg-red-500 text-white">Habis</span>
                                    @elseif ($product->stock <= $lowStockThreshold)
                                        <span class="badge bg-amber-300 text-zinc-950">{{ $product->stock }}</span>
                                    @else
                                        <span class="badge bg-brand-400 text-zinc-950">{{ $product->stock }}</span>
                                    @endif
                                </td>
                                <td class="table-td text-center">
                                    @if ($product->is_active)
                                        <span class="badge bg-brand-600 text-white">Aktif</span>
                                    @else
                                        <span class="badge bg-zinc-200 text-zinc-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="table-td text-right font-medium text-zinc-600">{{ $product->order_items_count }}</td>
                                <td class="table-td">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm">
                                            Ubah
                                        </a>

                                        <form method="POST" action="{{ route('admin.products.status', $product) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm">
                                                {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                              onclick="event.preventDefault(); confirmAction({
                                                  title: 'Hapus Produk',
                                                  message: 'Hapus produk ini? Produk dengan riwayat pesanan akan dinonaktifkan saja.',
                                                  confirmText: 'Ya, Hapus',
                                                  formAction: this.action,
                                                  formMethod: 'DELETE'
                                              })">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <p class="mt-3 text-xs font-medium text-zinc-500">
            Stok dianggap menipis bila {{ $lowStockThreshold }} unit atau kurang.
        </p>

        @if ($products->hasPages())
            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif
    @endif
@endsection
