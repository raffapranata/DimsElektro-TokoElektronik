@extends('layouts.admin')

@section('title', 'Ubah Produk')
@section('heading', 'Ubah Produk')

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="card p-6">
            @method('PUT')
            @include('admin.products._form')

            <div class="mt-6 flex flex-wrap items-center gap-2 border-t-2 border-zinc-900 pt-5">
                <button type="submit" class="btn btn-primary font-pixel text-[9px] uppercase">Simpan</button>
                <a href="{{ route('admin.products.index') }}" class="btn">Batal</a>

                <span class="ml-auto font-pixel text-[8px] text-zinc-500">{{ $product->slug }}</span>
            </div>
        </form>

        @if ($product->order_items_count > 0)
            <div class="mt-4 flex items-start gap-3 border-2 border-zinc-900 bg-amber-100 px-4 py-3 text-xs font-medium text-zinc-700 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                <svg class="mt-0.5 size-4 shrink-0 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 8v5m0 3.5v.01" stroke-linecap="square" />
                </svg>
                Produk ini muncul di {{ $product->order_items_count }} item pesanan, sehingga tidak dapat
                dihapus. Nonaktifkan saja agar tidak tampil di katalog.
            </div>
        @endif
    </div>
@endsection
