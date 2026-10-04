@props(['product', 'size' => 'size-20'])

<div {{ $attributes->merge(['class' => $size.' shrink-0 overflow-hidden rounded-none border-2 border-zinc-900 bg-amber-50']) }}>
    @if ($product->imageUrl())
        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-cover" style="image-rendering: pixelated;">
    @else
        <span class="flex h-full w-full items-center justify-center bg-[repeating-linear-gradient(45deg,#f7f4ef_0px,#f7f4ef_6px,#efece5_6px,#efece5_12px)]">
            <svg class="size-1/2" viewBox="0 0 96 72" fill="none" aria-hidden="true">
                <rect x="8" y="8" width="80" height="46" fill="#ffffff" stroke="#18181b" stroke-width="6"/>
                <rect x="38" y="54" width="20" height="10" fill="#18181b"/>
            </svg>
        </span>
    @endif
</div>
