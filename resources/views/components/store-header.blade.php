@props(['categories' => [], 'cartCount' => 0])

<header class="sticky top-0 z-40 border-b-2 border-zinc-900 bg-white">
    <!-- Row 1: logo, search, actions -->
    <div class="mx-auto flex w-full max-w-7xl items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="shrink-0 font-pixel text-[11px] text-zinc-900 sm:text-xs" aria-label="DimsElektro - beranda">
            Dims<span class="text-brand-600">Elektro</span>
        </a>

        <form method="GET" action="{{ route('products.index') }}" class="relative mx-auto hidden w-full max-w-xl items-center gap-2 md:flex" role="search">
            <svg class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.5-3.5" stroke-linecap="square" />
            </svg>
            <input type="search" name="q" value="{{ request()->query('q') }}"
                   placeholder="Cari laptop, monitor, aksesori..."
                   class="input-search"
                   aria-label="Cari produk">
            <button type="submit" class="btn btn-dark btn-sm shrink-0 font-pixel text-[9px] uppercase">Cari</button>
        </form>

        <div class="ml-auto flex items-center gap-2 md:ml-0">
            <a href="{{ route('cart.index') }}"
               class="icon-btn"
               aria-label="Keranjang belanja{{ $cartCount > 0 ? ", {$cartCount} item" : '' }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M3 4h2l2.4 10.4a2 2 0 0 0 2 1.6h7.4a2 2 0 0 0 2-1.5L20.5 8H6" stroke-linecap="round" stroke-linejoin="round" />
                    <circle cx="10" cy="20" r="1.2" />
                    <circle cx="18" cy="20" r="1.2" />
                </svg>

                @if ($cartCount > 0)
                    <span class="absolute -top-2.5 -right-2.5 grid size-5 place-items-center border-2 border-zinc-900 bg-amber-300 font-pixel text-[8px] text-zinc-950">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>

            @auth
                <details class="relative">
                    <summary class="icon-btn cursor-pointer list-none select-none" aria-label="Menu akun">
                        <span class="grid size-6 place-items-center border-2 border-zinc-900 bg-brand-500 font-pixel text-[9px] text-zinc-950 uppercase">
                            {{ mb_substr(trim(auth()->user()->name), 0, 1) }}
                        </span>
                    </summary>

                    <div class="dropdown-panel right-0 w-60">
                        <div class="border-b-2 border-zinc-900 px-4 py-2.5">
                            <p class="truncate text-sm font-bold text-zinc-900">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-zinc-500">{{ auth()->user()->email }}</p>
                        </div>

                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="dropdown-item font-bold text-brand-700">&gt; Panel Admin</a>
                        @else
                            <a href="{{ route('orders.index') }}" class="dropdown-item">&gt; Pesanan Saya</a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item w-full text-left text-red-700 hover:bg-red-50">
                                &gt; Keluar
                            </button>
                        </form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="hidden rounded-none px-3 py-2 text-sm font-bold text-zinc-900 underline decoration-2 underline-offset-2 transition-colors hover:text-brand-700 sm:block">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
            @endauth

            <details class="relative md:hidden">
                <summary class="icon-btn cursor-pointer list-none select-none" aria-label="Menu navigasi">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="square" />
                    </svg>
                </summary>

                <div class="dropdown-panel right-0 w-64">
                    <form method="GET" action="{{ route('products.index') }}" class="border-b-2 border-zinc-900 p-3" role="search">
                        <div class="flex gap-2">
                            <input type="search" name="q" value="{{ request()->query('q') }}"
                                   placeholder="Cari produk..." class="input-search !pl-3" aria-label="Cari produk">
                            <button type="submit" class="btn btn-dark btn-sm shrink-0 font-pixel text-[9px]">OK</button>
                        </div>
                    </form>

                    <a href="{{ route('products.index') }}" class="dropdown-item font-bold">&gt; Semua produk</a>
                    @foreach ($categories->take(6) as $category)
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="dropdown-item">
                            &gt; {{ $category->name }}
                        </a>
                    @endforeach

                    @guest
                        <div class="mt-1 border-t-2 border-zinc-900 pt-1.5">
                            <a href="{{ route('login') }}" class="dropdown-item">&gt; Masuk</a>
                            <a href="{{ route('register') }}" class="dropdown-item font-bold text-brand-700">&gt; Daftar</a>
                        </div>
                    @endauth
                </div>
            </details>
        </div>
    </div>

    <!-- Row 2: amber arcade category rail -->
    <div class="border-t-2 border-zinc-900 bg-amber-300">
        <div class="mx-auto flex w-full max-w-7xl items-center gap-1 overflow-x-auto px-4 py-1.5 sm:px-6 lg:px-8 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <a href="{{ route('products.index') }}"
               @class(['nav-link whitespace-nowrap', 'nav-link-active' => request()->routeIs('products.*') && ! request()->query('category')])>
                Semua Produk
            </a>

            @foreach ($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                   @class(['nav-link whitespace-nowrap', 'nav-link-active' => request()->query('category') === $category->slug])>
                    {{ $category->name }}
                </a>
            @endforeach

            @auth
                @unless (auth()->user()->isAdmin())
                    <a href="{{ route('orders.index') }}"
                       @class(['ml-auto nav-link whitespace-nowrap', 'nav-link-active' => request()->routeIs('orders.*')])>
                        Pesanan Saya
                    </a>
                @endunless
            @endauth
        </div>
    </div>
</header>
