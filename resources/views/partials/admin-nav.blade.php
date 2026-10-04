@php
    // The same partial renders the sidebar (compact = false) and the mobile
    // top-bar tab strip (compact = true); only the active/idle classes differ.
    $links = [
        ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12h4l2 5 4-10 2 5h6'],
        ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => 'Produk', 'icon' => 'M20 7H4m16 5H4m16 5H4'],
        ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Kategori', 'icon' => 'M4 5h7v7H4zM13 5h7v7h-7zM4 14h7v5H4zM13 14h7v5h-7z'],
        ['route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'label' => 'Pesanan', 'icon' => 'M6 4h12l2 4-8 12L4 8l2-4Zm-2 4h16M10 8l2 12 2-12'],
    ];

    $activeClasses = $compact
        ? 'border-zinc-900 bg-brand-500 text-zinc-950'
        : 'admin-link-active';
    $idleClasses = $compact
        ? 'text-zinc-600 hover:bg-amber-100 hover:text-zinc-900'
        : 'admin-link-idle';
@endphp

@foreach ($links as $link)
    <a href="{{ route($link['route']) }}"
       @class([$compact ? 'whitespace-nowrap rounded-none border-2 px-3 py-2 text-sm font-bold transition-colors duration-150' : 'admin-link', $activeClasses => request()->routeIs($link['pattern']), $idleClasses => ! request()->routeIs($link['pattern'])])
       @if (request()->routeIs($link['pattern'])) aria-current="page" @endif>
        <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="{{ $link['icon'] }}" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        {{ $link['label'] }}
    </a>
@endforeach
