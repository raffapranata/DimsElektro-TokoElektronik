@extends('layouts.admin')

@section('title', 'Ubah Kategori')
@section('heading', 'Ubah Kategori')

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="card p-6">
            @method('PUT')
            @include('admin.categories._form')

            <div class="mt-6 flex flex-wrap items-center gap-2 border-t-2 border-zinc-900 pt-5">
                <button type="submit" class="btn btn-primary font-pixel text-[9px] uppercase">Simpan</button>
                <a href="{{ route('admin.categories.index') }}" class="btn">Batal</a>

                @if ($category->products_count > 0)
                    <span class="ml-auto text-xs font-medium text-zinc-500">
                        {{ $category->products_count }} produk menggunakan kategori ini
                    </span>
                @endif
            </div>
        </form>

        <div class="mt-4 flex items-start gap-3 border-2 border-zinc-900 bg-amber-100 px-4 py-3 text-xs font-medium text-zinc-700 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
            <svg class="mt-0.5 size-4 shrink-0 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                <circle cx="12" cy="12" r="9" />
                <path d="M12 8v5m0 3.5v.01" stroke-linecap="square" />
            </svg>
            Kategori dengan produk aktif tidak dapat dihapus. Pindahkan produknya terlebih dahulu
            agar riwayat pesanan tetap utuh.
        </div>
    </div>
@endsection
