@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'rounded-none border-2 border-dashed border-zinc-900 bg-white px-6 py-14 text-center']) }}>
    <!-- Pixel-art treasure chest -->
    <svg class="mx-auto" width="110" height="90" viewBox="0 0 110 90" fill="none" aria-hidden="true">
        <rect x="10" y="40" width="90" height="42" fill="#F97316" stroke="#18181b" stroke-width="4"/>
        <rect x="10" y="40" width="90" height="14" fill="#10B981" stroke="#18181b" stroke-width="4"/>
        <rect x="46" y="36" width="18" height="22" fill="#FDE047" stroke="#18181b" stroke-width="4"/>
        <rect x="52" y="44" width="6" height="8" fill="#18181b"/>
        <rect x="18" y="58" width="12" height="8" fill="#FDE047"/>
        <rect x="80" y="58" width="12" height="8" fill="#FDE047"/>
        <rect x="18" y="74" width="12" height="8" fill="#FDE047"/>
        <rect x="80" y="74" width="12" height="8" fill="#FDE047"/>
        <!-- Sparkles -->
        <rect x="2" y="18" width="8" height="8" fill="#18181b"/>
        <rect x="96" y="8" width="8" height="8" fill="#18181b"/>
        <rect x="100" y="22" width="6" height="6" fill="#F97316"/>
        <rect x="6" y="4" width="6" height="6" fill="#10B981"/>
    </svg>

    <p class="mt-5 font-pixel text-[11px] leading-relaxed text-zinc-900">{{ $title }}</p>

    @if ($description)
        <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed font-medium text-zinc-600">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-6 flex justify-center gap-3">{{ $action }}</div>
    @endisset
</div>
