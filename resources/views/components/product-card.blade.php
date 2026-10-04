@props(['product'])

<article class="card card-hover group relative flex flex-col overflow-hidden">
    <a href="{{ route('products.show', $product) }}" class="relative block aspect-[4/3] w-full overflow-hidden border-b-2 border-zinc-900 bg-amber-50">
        @if ($product->imageUrl())
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy"
                 class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                 style="image-rendering: pixelated;">
        @else
            <!-- Pixel-art placeholder so a missing photo still reads as intentional design. -->
            <span class="flex h-full w-full items-center justify-center bg-[repeating-linear-gradient(45deg,#f7f4ef_0px,#f7f4ef_10px,#efece5_10px,#efece5_20px)]">
                <svg width="96" height="72" viewBox="0 0 96 72" fill="none" aria-hidden="true">
                    <rect x="8" y="8" width="80" height="46" fill="#ffffff" stroke="#18181b" stroke-width="4"/>
                    <rect x="16" y="16" width="26" height="10" fill="#10B981"/>
                    <rect x="16" y="34" width="40" height="10" fill="#F97316"/>
                    <rect x="38" y="54" width="20" height="10" fill="#18181b"/>
                    <rect x="24" y="62" width="48" height="4" fill="#18181b"/>
                </svg>
            </span>
        @endif

        @if (! $product->isInStock())
            <span class="badge absolute top-3 left-3 bg-zinc-900 text-white">Habis</span>
        @elseif ($product->stock <= 5)
            <span class="badge absolute top-3 left-3 bg-red-500 text-white">Sisa {{ $product->stock }}</span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-4">
        <p class="font-pixel text-[8px] leading-relaxed text-brand-700">{{ strtoupper($product->category->name) }}</p>

        <h3 class="mt-2 line-clamp-2 text-sm font-bold leading-snug text-zinc-900">
            <a href="{{ route('products.show', $product) }}" class="transition-colors after:absolute after:inset-0 hover:text-brand-700">
                {{ $product->name }}
            </a>
        </h3>

        <p class="mt-1 text-xs font-medium text-zinc-500">{{ $product->brand }}</p>

        <div class="mt-auto pt-3">
            <p class="price-tag text-sm">
                <x-money :amount="$product->price" />
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1">
                @if ($product->isInStock())
                    <span class="badge bg-brand-200 text-brand-900">Stok {{ $product->stock }}</span>
                @else
                    <span class="badge bg-zinc-200 text-zinc-600">Tidak tersedia</span>
                @endif

                @if ($product->warranty_months > 0)
                    <span class="badge bg-white text-zinc-700">{{ $product->warranty_months }} bln</span>
                @endif
            </div>

            @if ($product->isInStock())
                <form method="POST" action="{{ route('cart.store') }}" class="relative z-10 mt-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-primary btn-sm w-full font-pixel text-[8px] uppercase">
                        + Keranjang
                    </button>
                </form>
            @else
                <button type="button" disabled class="btn btn-secondary btn-sm mt-3 w-full font-pixel text-[8px] uppercase">Habis</button>
            @endif
        </div>
    </div>
</article>
