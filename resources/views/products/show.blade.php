@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <nav class="flex flex-wrap items-center gap-1 text-sm font-medium text-zinc-500" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="underline decoration-2 underline-offset-2 transition-colors hover:text-zinc-900">Beranda</a>
        <span class="text-zinc-300">/</span>
        <a href="{{ route('products.index') }}" class="underline decoration-2 underline-offset-2 transition-colors hover:text-zinc-900">Produk</a>
        <span class="text-zinc-300">/</span>
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="underline decoration-2 underline-offset-2 transition-colors hover:text-zinc-900">
            {{ $product->category->name }}
        </a>
        <span class="text-zinc-300">/</span>
        <span class="truncate font-bold text-zinc-900">{{ $product->name }}</span>
    </nav>

    <div class="mt-5 grid grid-cols-1 gap-8 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="aspect-[4/3] w-full bg-amber-50">
                @if ($product->imageUrl())
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                         class="h-full w-full object-cover" style="image-rendering: pixelated;">
                @else
                    <span class="flex h-full w-full items-center justify-center bg-[repeating-linear-gradient(45deg,#f7f4ef_0px,#f7f4ef_14px,#efece5_14px,#efece5_28px)]">
                        <svg width="140" height="104" viewBox="0 0 96 72" fill="none" aria-hidden="true">
                            <rect x="8" y="8" width="80" height="46" fill="#ffffff" stroke="#18181b" stroke-width="4"/>
                            <rect x="16" y="16" width="26" height="10" fill="#10B981"/>
                            <rect x="16" y="34" width="40" height="10" fill="#F97316"/>
                            <rect x="38" y="54" width="20" height="10" fill="#18181b"/>
                            <rect x="24" y="62" width="48" height="4" fill="#18181b"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>

        <div>
            <p class="overline-label">{{ strtoupper($product->brand) }}</p>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-zinc-900 sm:text-3xl">{{ $product->name }}</h1>

            <p class="price-tag mt-4 text-xl">
                <x-money :amount="$product->price" />
            </p>

            <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
                <div class="border-2 border-zinc-900 bg-white px-3.5 py-2.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <dt class="text-xs font-medium text-zinc-500">Kategori</dt>
                    <dd class="mt-0.5 font-bold text-zinc-900">{{ $product->category->name }}</dd>
                </div>
                <div class="border-2 border-zinc-900 bg-white px-3.5 py-2.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <dt class="text-xs font-medium text-zinc-500">Ketersediaan</dt>
                    <dd class="mt-0.5 font-bold {{ $product->isInStock() ? 'text-brand-700' : 'text-red-700' }}">
                        {{ $product->isInStock() ? 'Stok '.$product->stock.' unit' : 'Stok habis' }}
                    </dd>
                </div>
                <div class="border-2 border-zinc-900 bg-white px-3.5 py-2.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <dt class="text-xs font-medium text-zinc-500">Garansi</dt>
                    <dd class="mt-0.5 font-bold text-zinc-900">
                        {{ $product->warranty_months > 0 ? $product->warranty_months.' bulan' : 'Tanpa garansi' }}
                    </dd>
                </div>
                <div class="border-2 border-zinc-900 bg-white px-3.5 py-2.5 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <dt class="text-xs font-medium text-zinc-500">Merek</dt>
                    <dd class="mt-0.5 font-bold text-zinc-900">{{ $product->brand }}</dd>
                </div>
            </div>

            @if ($product->isInStock())
                <form method="POST" action="{{ route('cart.store') }}" class="mt-8 flex flex-wrap items-end gap-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div class="w-28">
                        <label for="quantity" class="label">Jumlah</label>
                        <input id="quantity" name="quantity" type="number" value="1"
                               min="1" max="{{ $product->stock }}" step="1"
                               class="input mt-1.5 @error('quantity') input-error @enderror">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        + Keranjang
                    </button>
                    <a href="{{ route('cart.index') }}" class="btn">Lihat keranjang</a>
                </form>

                <x-input-error name="quantity" />
                <x-input-error name="product_id" />
            @else
                <x-alert type="warning" class="mt-8">
                    Stok habis. Produk ini sedang tidak dapat dipesan.
                </x-alert>
            @endif
        </div>
    </div>

    <section class="mt-14">
        <p class="overline-label">// DETAIL TEKNIS</p>
        <h2 class="section-title mt-1.5">Spesifikasi</h2>

        @if (blank($product->specs))
            <x-empty-state class="mt-4" title="Spesifikasi belum tersedia" />
        @else
            <div class="card mt-4 overflow-hidden">
                <dl class="divide-y-2 divide-zinc-900">
                    @foreach ($product->specs as $key => $value)
                        <div class="grid grid-cols-1 gap-1 px-5 py-3.5 transition-colors hover:bg-amber-50 sm:grid-cols-3">
                            <dt class="text-sm font-bold text-zinc-500">{{ $key }}</dt>
                            <dd class="text-sm text-zinc-900 sm:col-span-2">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="mt-14">
            <p class="overline-label">// REKOMENDASI</p>
            <h2 class="section-title mt-1.5">Produk lain di {{ $product->category->name }}</h2>

            <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($relatedProducts as $relatedProduct)
                    <x-product-card :product="$relatedProduct" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
