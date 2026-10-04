@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <!-- Hero: retro game-case box -->
    <section class="relative overflow-hidden border-2 border-zinc-900 bg-amber-300 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]" aria-hidden="true">
            <div class="h-full w-full [background-image:repeating-linear-gradient(0deg,transparent,transparent_5px,black_5px,black_6px)]"></div>
        </div>

        <div class="relative grid grid-cols-1 items-center gap-8 px-6 py-12 sm:px-12 lg:grid-cols-[1fr_auto] lg:px-16 lg:py-14">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 border-2 border-zinc-900 bg-zinc-900 px-3 py-1.5 font-pixel text-[9px] text-amber-300">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M13 2 4.5 13.5H11L10 22l8.5-11.5H13L13 2Z" />
                    </svg>
                    GRATIS ONGKIR &middot; GARANSI RESMI
                </span>

                <h1 class="mt-5 text-3xl font-black leading-tight tracking-tight text-zinc-950 sm:text-4xl lg:text-5xl">
                    Peralatan kerja <span class="bg-brand-500 px-1 [box-shadow:3px_3px_0px_0px_rgba(0,0,0,1)]">andal</span>,<br>
                    tanpa drama.
                </h1>

                <p class="mt-4 max-w-xl text-sm leading-relaxed font-medium text-zinc-800 sm:text-base">
                    Laptop, monitor, smartphone, hingga aksesori — semua dikurasi, bergaransi resmi,
                    dan dikirim langsung dari gudang kami ke seluruh Indonesia.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('products.index') }}" class="btn btn-primary">
                        Jelajahi katalog
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="square" />
                        </svg>
                    </a>

                    @guest
                        <a href="{{ route('register') }}" class="btn">Buat akun</a>
                    @endguest
                </div>

                <dl class="mt-10 flex flex-wrap gap-x-10 gap-y-4 border-t-2 border-zinc-900 pt-6">
                    <div>
                        <dd class="font-pixel text-sm text-zinc-950">{{ $categories->count() }}+</dd>
                        <dt class="mt-1.5 text-xs font-bold text-zinc-700 uppercase">Kategori kurasi</dt>
                    </div>
                    <div>
                        <dd class="font-pixel text-sm text-zinc-950">{{ $featuredProducts->count() }}</dd>
                        <dt class="mt-1.5 text-xs font-bold text-zinc-700 uppercase">Rilis terbaru</dt>
                    </div>
                    <div>
                        <dd class="font-pixel text-sm text-zinc-950">12</dd>
                        <dt class="mt-1.5 text-xs font-bold text-zinc-700 uppercase">Garansi bulan</dt>
                    </div>
                </dl>
            </div>

            <!-- Pixel-art electronics: monitor, console, smartphone -->
            <div class="hidden lg:block" aria-hidden="true">
                <svg width="300" height="250" viewBox="0 0 300 250" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Monitor -->
                    <rect x="30" y="20" width="150" height="100" fill="#ffffff" stroke="#18181b" stroke-width="4"/>
                    <rect x="44" y="34" width="122" height="72" fill="#10B981" stroke="#18181b" stroke-width="4"/>
                    <rect x="56" y="46" width="98" height="10" fill="#ffffff"/>
                    <rect x="56" y="64" width="70" height="10" fill="#ffffff"/>
                    <rect x="56" y="82" width="84" height="10" fill="#ffffff"/>
                    <rect x="93" y="120" width="24" height="18" fill="#18181b"/>
                    <rect x="65" y="138" width="80" height="12" fill="#ffffff" stroke="#18181b" stroke-width="4"/>

                    <!-- Handheld console -->
                    <rect x="200" y="40" width="70" height="110" fill="#ffffff" stroke="#18181b" stroke-width="4"/>
                    <rect x="210" y="52" width="50" height="42" fill="#18181b"/>
                    <rect x="214" y="56" width="18" height="8" fill="#F97316"/>
                    <rect x="238" y="56" width="18" height="8" fill="#10B981"/>
                    <rect x="214" y="72" width="18" height="8" fill="#10B981"/>
                    <rect x="238" y="72" width="18" height="8" fill="#F97316"/>
                    <rect x="212" y="104" width="14" height="14" fill="#18181b"/>
                    <rect x="244" y="104" width="14" height="14" fill="#18181b"/>
                    <rect x="218" y="108" width="3" height="6" fill="#ffffff"/>
                    <rect x="216" y="110" width="7" height="3" fill="#ffffff"/>
                    <rect x="249" y="108" width="3" height="6" fill="#ffffff"/>
                    <rect x="247" y="110" width="7" height="3" fill="#ffffff"/>

                    <!-- Smartphone -->
                    <rect x="210" y="165" width="50" height="76" fill="#ffffff" stroke="#18181b" stroke-width="4"/>
                    <rect x="218" y="176" width="34" height="46" fill="#F97316"/>
                    <circle cx="235" cy="230" r="4" fill="#18181b"/>

                    <!-- Sparkles -->
                    <rect x="10" y="180" width="8" height="8" fill="#18181b"/>
                    <rect x="24" y="194" width="8" height="8" fill="#18181b"/>
                    <rect x="160" y="8" width="8" height="8" fill="#18181b"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section class="mt-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="overline-label">// KATEGORI</p>
                <h2 class="section-title mt-1.5">Belanja per kategori</h2>
            </div>
            <a href="{{ route('products.index') }}" class="link text-sm">Lihat semua &rarr;</a>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                   class="card card-hover group flex items-center justify-between gap-2 px-4 py-3.5">
                    <span>
                        <span class="block text-sm font-bold text-zinc-900">{{ $category->name }}</span>
                        <span class="mt-0.5 block text-xs font-medium text-zinc-500">
                            {{ $category->active_products_count }} produk
                        </span>
                    </span>
                    <span class="grid size-7 shrink-0 place-items-center border-2 border-zinc-900 bg-amber-300 transition-transform duration-200 group-hover:translate-x-0.5">
                        <svg class="size-3.5 text-zinc-950" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                            <path d="M9 6l6 6-6 6" stroke-linecap="square" stroke-linejoin="round" />
                        </svg>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Featured products -->
    <section class="mt-14">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="overline-label">// BARU DATANG</p>
                <h2 class="section-title mt-1.5">Produk terbaru</h2>
            </div>
            <a href="{{ route('products.index') }}" class="link text-sm">Lihat semua &rarr;</a>
        </div>

        @if ($featuredProducts->isEmpty())
            <x-empty-state
                class="mt-4"
                title="Belum ada produk tersedia"
                description="Peti harta masih kosong! Katalog akan tampil di sini setelah admin mengisi produk.">
                <x-slot:action>
                    @auth
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Isi peti harta</a>
                        @endif
                    @endauth
                </x-slot:action>
            </x-empty-state>
        @else
            <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>
@endsection
