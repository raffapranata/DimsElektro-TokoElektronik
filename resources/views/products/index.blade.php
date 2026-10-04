@extends('layouts.app')

@section('title', $activeCategory?->name ?? 'Katalog Produk')

@section('content')
    <div class="flex flex-wrap items-baseline justify-between gap-2">
        <div>
            <p class="overline-label">// KATALOG</p>
            <h1 class="section-title mt-1.5">{{ $activeCategory?->name ?? 'Semua Produk' }}</h1>
            <p class="mt-1 text-sm font-medium text-zinc-500">
                {{ $products->total() }} produk ditemukan
                @if ($filters['q'])
                    untuk pencarian &ldquo;<span class="font-bold text-zinc-900">{{ $filters['q'] }}</span>&rdquo;
                @endif
            </p>
        </div>
    </div>

    <form method="GET" action="{{ route('products.index') }}" class="card mt-5 p-4 sm:p-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="q" class="label">Cari produk</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] }}"
                       placeholder="Nama atau merek" class="input mt-1.5">
            </div>

            <div>
                <label for="category" class="label">Kategori</label>
                <select id="category" name="category" class="input mt-1.5">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="brand" class="label">Merek</label>
                <select id="brand" name="brand" class="input mt-1.5">
                    <option value="">Semua merek</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand }}" @selected($filters['brand'] === $brand)>{{ $brand }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="sort" class="label">Urutkan</label>
                <select id="sort" name="sort" class="input mt-1.5">
                    @foreach ($sorts as $sort)
                        <option value="{{ $sort->value }}" @selected($filters['sort'] === $sort)>
                            {{ $sort->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2 border-t-2 border-zinc-900 pt-4">
            <button type="submit" class="btn btn-primary font-pixel text-[9px] uppercase">Terapkan</button>
            <a href="{{ route('products.index') }}" class="btn">Reset</a>
        </div>
    </form>

    @if ($products->isEmpty())
        <x-empty-state
            class="mt-8"
            title="Produk tidak ditemukan"
            description="Peti harta kosong untuk filter ini. Coba ubah kata kunci, kategori, atau merek yang Anda pilih.">
            <x-slot:action>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Reset pencarian</a>
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>

        @if ($products->hasPages())
            <div class="mt-10">
                {{ $products->links() }}
            </div>
        @endif
    @endif
@endsection
