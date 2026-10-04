@extends('layouts.admin')

@section('title', 'Kategori')
@section('heading', 'Kategori')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-wrap items-center gap-2">
            <label for="q" class="sr-only">Cari kategori</label>
            <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Cari nama kategori"
                   class="input w-56">

            <button type="submit" class="btn font-pixel text-[9px] uppercase">Cari</button>

            @if ($search)
                <a href="{{ route('admin.categories.index') }}" class="btn">Reset</a>
            @endif
        </form>

        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary font-pixel text-[9px] uppercase">
            + Tambah
        </a>
    </div>

    @if ($categories->isEmpty())
        <x-empty-state
            class="mt-4"
            title="{{ $search ? 'Kategori tidak ditemukan' : 'Belum ada kategori' }}"
            description="{{ $search ? 'Coba kata kunci lain.' : 'Tambahkan kategori pertama untuk mulai mengelompokkan produk.' }}">
            <x-slot:action>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Tambah kategori</a>
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="card mt-4 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y-2 divide-zinc-900">
                    <thead class="bg-amber-50">
                        <tr>
                            <th scope="col" class="table-th">Nama</th>
                            <th scope="col" class="table-th">Slug</th>
                            <th scope="col" class="table-th">Deskripsi</th>
                            <th scope="col" class="table-th text-right">Produk</th>
                            <th scope="col" class="table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-zinc-100">
                        @foreach ($categories as $category)
                            <tr class="table-row-hover">
                                <td class="table-td font-bold text-zinc-900">{{ $category->name }}</td>
                                <td class="table-td font-pixel text-[8px] text-zinc-500">{{ $category->slug }}</td>
                                <td class="table-td max-w-md truncate font-medium text-zinc-500">
                                    {{ $category->description ?: '-' }}
                                </td>
                                <td class="table-td text-right">
                                    <span class="badge bg-white text-zinc-700">{{ $category->products_count }}</span>
                                </td>
                                <td class="table-td">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm">
                                            Ubah
                                        </a>

                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                              onclick="event.preventDefault(); confirmAction({
                                                  title: 'Hapus Kategori',
                                                  message: 'Hapus kategori ini?',
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

        @if ($categories->hasPages())
            <div class="mt-6">
                {{ $categories->links() }}
            </div>
        @endif
    @endif
@endsection
