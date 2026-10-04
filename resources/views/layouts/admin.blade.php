<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') &middot; Admin {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-cream font-sans text-zinc-900 antialiased">
    <div class="flex min-h-full">
        <!-- Sidebar (desktop) -->
        <aside class="sticky top-0 hidden h-dvh w-60 shrink-0 flex-col border-r-2 border-zinc-900 bg-zinc-950 text-zinc-300 lg:flex">
            <div class="px-5 py-5">
                <a href="{{ route('admin.dashboard') }}" class="font-pixel text-[10px] text-white">
                    Dims<span class="text-brand-400">Elektro</span>
                </a>
                <p class="mt-2 font-pixel text-[8px] text-amber-300">PANEL ADMIN</p>
            </div>

            <nav class="flex flex-1 flex-col gap-1.5 px-3" aria-label="Navigasi admin">
                @include('partials.admin-nav', ['compact' => false])
            </nav>

            <div class="border-t border-white/10 p-3">
                <a href="{{ route('home') }}"
                   class="flex items-center gap-2.5 border-2 border-transparent px-3 py-2 text-sm font-semibold text-zinc-400 transition-all duration-150 hover:border-white hover:bg-white hover:text-zinc-950">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M17 17V9m0 8-3-3m3 3 3-3M7 7v8m0 0-3-3m3 3 3-3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Lihat toko
                </a>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-40 border-b-2 border-zinc-900 bg-white">
                <div class="flex items-center gap-3 px-4 py-3 sm:px-6">
                    <a href="{{ route('admin.dashboard') }}" class="font-pixel text-[9px] text-zinc-950 lg:hidden">
                        Dims<span class="text-brand-600">Elektro</span>
                    </a>

                    <h1 class="hidden text-sm font-bold text-zinc-900 lg:block">@yield('heading', 'Dashboard')</h1>

                    <div class="ml-auto flex items-center gap-2">
                        <a href="{{ route('home') }}" class="btn btn-secondary btn-sm lg:hidden">Toko</a>

                        <details class="relative">
                            <summary class="flex cursor-pointer list-none items-center gap-2 select-none">
                                <span class="grid size-9 place-items-center border-2 border-zinc-900 bg-brand-500 font-pixel text-[10px] text-zinc-950 uppercase shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                    {{ mb_substr(trim(auth()->user()->name), 0, 1) }}
                                </span>
                                <span class="hidden max-w-[9rem] truncate text-sm font-bold text-zinc-900 sm:block">{{ auth()->user()->name }}</span>
                                <svg class="size-3.5 text-zinc-900" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.2 7.2a1 1 0 0 1 1.4 0L10 10.6l3.4-3.4a1 1 0 1 1 1.4 1.4l-4.1 4.1a1 1 0 0 1-1.4 0L5.2 8.6a1 1 0 0 1 0-1.4Z" clip-rule="evenodd" />
                                </svg>
                            </summary>

                            <div class="dropdown-panel right-0 w-60">
                                <div class="border-b-2 border-zinc-900 px-4 py-2">
                                    <p class="text-xs text-zinc-500">Masuk sebagai</p>
                                    <p class="truncate text-sm font-bold text-zinc-900">{{ auth()->user()->email }}</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item w-full text-left text-red-700 hover:bg-red-50">
                                        &gt; Keluar
                                    </button>
                                </form>
                            </div>
                        </details>
                    </div>
                </div>

                <nav class="flex gap-1 overflow-x-auto px-4 pb-2 sm:px-6 lg:hidden" aria-label="Navigasi admin">
                    @include('partials.admin-nav', ['compact' => true])
                </nav>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6">
                <div class="mb-4 lg:hidden">
                    <h1 class="section-title">@yield('heading', 'Dashboard')</h1>
                </div>

                @include('partials.flash')

                @yield('content')
            </main>
        </div>
    </div>

    @include('components.toast-notification')
    @include('components.confirmation-modal')

    @stack('scripts')
</body>
</html>
