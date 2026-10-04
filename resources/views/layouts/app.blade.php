<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Katalog Produk') &middot; {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta_description', config('app.name').' - pusat elektronik dari laptop, monitor, hingga aksesori.')">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-cream font-sans text-zinc-900 antialiased">
    <x-store-header :categories="$navCategories" :cart-count="$cartItemCount" />

    <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
        @include('partials.flash')

        @yield('content')
    </main>

    <footer class="mt-auto border-t-4 border-zinc-900 bg-white">
        <div class="mx-auto grid w-full max-w-7xl gap-8 px-4 py-10 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            <div>
                <a href="{{ route('home') }}" class="font-pixel text-xs text-zinc-900">
                    Dims<span class="text-brand-600">Elektro</span>
                </a>
                <p class="mt-3 max-w-xs text-sm leading-relaxed text-zinc-600">
                    Pusat elektronik pilihan: laptop, monitor, smartphone, hingga aksesori kerja — dikurasi dan dikirim dari gudang sendiri.
                </p>
            </div>

            <div>
                <p class="font-pixel text-[10px] text-zinc-900">BELANJA</p>
                <ul class="mt-3 space-y-2 text-sm font-medium">
                    <li><a href="{{ route('products.index') }}" class="underline decoration-2 underline-offset-2 transition-colors hover:text-brand-700">Semua produk</a></li>
                    @foreach ($navCategories->take(4) as $category)
                        <li>
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="underline decoration-2 underline-offset-2 transition-colors hover:text-brand-700">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="font-pixel text-[10px] text-zinc-900">AKUN</p>
                <ul class="mt-3 space-y-2 text-sm font-medium">
                    @auth
                        <li><a href="{{ route('orders.index') }}" class="underline decoration-2 underline-offset-2 transition-colors hover:text-brand-700">Pesanan saya</a></li>
                        <li><a href="{{ route('cart.index') }}" class="underline decoration-2 underline-offset-2 transition-colors hover:text-brand-700">Keranjang</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="underline decoration-2 underline-offset-2 transition-colors hover:text-brand-700">Masuk</a></li>
                        <li><a href="{{ route('register') }}" class="underline decoration-2 underline-offset-2 transition-colors hover:text-brand-700">Daftar</a></li>
                    @endauth
                </ul>
            </div>

            <div>
                <p class="font-pixel text-[10px] text-zinc-900">JAMINAN</p>
                <ul class="mt-3 space-y-2 text-sm font-medium">
                    <li class="flex items-center gap-2">
                        <span class="grid size-6 shrink-0 place-items-center border-2 border-zinc-900 bg-brand-500">
                            <svg class="size-3.5 text-zinc-950" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                <path d="m5 13 4 4L19 7" stroke-linecap="square" />
                            </svg>
                        </span>
                        Garansi resmi
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="grid size-6 shrink-0 place-items-center border-2 border-zinc-900 bg-amber-300">
                            <svg class="size-3.5 text-zinc-950" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                <path d="M3 7h11v8H3zM14 10h4l3 3v2h-7z" stroke-linecap="square" stroke-linejoin="round" />
                                <circle cx="7" cy="17.5" r="1.5" />
                                <circle cx="17" cy="17.5" r="1.5" />
                            </svg>
                        </span>
                        Pengiriman seluruh Indonesia
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t-2 border-zinc-900">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-1 px-4 py-4 text-xs font-medium text-zinc-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>&copy; {{ now()->year }} {{ config('app.name') }}. Seluruh hak cipta.</p>
                <p class="font-pixel text-[9px] text-zinc-900">INSERT COIN TO SHOP</p>
            </div>
        </div>
    </footer>
</body>
</html>
