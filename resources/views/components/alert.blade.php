@props(['type' => 'info'])

@php
    $styles = [
        'success' => 'border-zinc-900 bg-brand-200 text-brand-950',
        'error' => 'border-zinc-900 bg-red-100 text-red-900',
        'warning' => 'border-zinc-900 bg-amber-200 text-amber-950',
        'info' => 'border-zinc-900 bg-white text-zinc-800',
    ];

    $tiles = [
        'success' => 'bg-brand-500',
        'error' => 'bg-red-500',
        'warning' => 'bg-amber-400',
        'info' => 'bg-zinc-300',
    ];
@endphp

<div role="alert" {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-none border-2 border-zinc-900 px-4 py-3 text-sm font-medium shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] '.($styles[$type] ?? $styles['info'])]) }}>
    <span class="mt-1 size-3 shrink-0 border-2 border-zinc-900 {{ $tiles[$type] ?? $tiles['info'] }}" aria-hidden="true"></span>

    <div class="min-w-0 flex-1">{{ $slot }}</div>
</div>
