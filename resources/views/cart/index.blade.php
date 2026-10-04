@extends('layouts.app')

@section('title', 'Keranjang')

@section('content')
    <p class="overline-label">// KERANJANG</p>
    <h1 class="section-title mt-1.5">Belanja Anda</h1>

    @if ($lines === [])
        <x-empty-state
            class="mt-6"
            title="Keranjang Anda masih kosong"
            description="Tambahkan produk dari katalog untuk mulai berbelanja.">
            <x-slot:action>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Lihat katalog</a>
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                @foreach ($lines as $line)
                    @php($product = $line['product'])

                    <div class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                        <x-product-thumb :product="$product" />

                        <div class="min-w-0 flex-1">
                            <a href="{{ route('products.show', $product) }}"
                               class="text-sm font-bold text-zinc-900 underline decoration-2 underline-offset-2 transition-colors hover:text-brand-700">
                                {{ $product->name }}
                            </a>
                            <p class="mt-0.5 text-xs font-medium text-zinc-500">
                                {{ $product->brand }} &middot; {{ $product->category->name }}
                            </p>
                            <p class="mt-1 text-sm text-zinc-600">
                                <x-money :amount="$product->price" /> / unit
                                <span class="text-zinc-300">&middot;</span>
                                stok {{ $product->stock }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('cart.update', $product) }}" class="flex items-end gap-2">
                            @csrf
                            @method('PATCH')

                            <div class="w-24">
                                <label for="quantity-{{ $product->id }}" class="label text-xs">Jumlah</label>
                                <input id="quantity-{{ $product->id }}" name="quantity" type="number"
                                       value="{{ $line['quantity'] }}" min="1" max="{{ $product->stock }}" step="1"
                                       class="input mt-1.5">
                            </div>

                            <button type="submit" class="btn btn-sm">OK</button>
                        </form>

                        <div class="text-left sm:w-32 sm:text-right">
                            <p class="font-pixel text-[10px] text-zinc-900">
                                <x-money :amount="$line['subtotal']" />
                            </p>

                            <form method="POST" action="{{ route('cart.destroy', $product) }}" class="mt-1.5">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-red-700 underline decoration-2 underline-offset-2 transition-colors hover:text-red-900">
                                    [ Hapus ]
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <aside class="lg:col-span-1">
                <div class="card sticky top-36 p-5">
                    <h2 class="font-pixel text-[10px] text-zinc-900">RINGKASAN</h2>

                    <dl class="mt-4 space-y-2.5 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="font-medium text-zinc-500">Subtotal</dt>
                            <dd class="font-bold text-zinc-900"><x-money :amount="$subtotal" /></dd>
                        </div>

                        <div class="flex items-center justify-between">
                            <dt class="font-medium text-zinc-500">Pengiriman</dt>
                            <dd class="font-bold text-zinc-900">
                                @if ($shippingCost > 0)
                                    <x-money :amount="$shippingCost" />
                                @else
                                    <span class="text-brand-700">Gratis</span>
                                @endif
                            </dd>
                        </div>

                        <div class="flex items-center justify-between border-t-2 border-zinc-900 pt-3">
                            <dt class="font-black text-zinc-900">Total</dt>
                            <dd class="font-pixel text-xs text-zinc-950">
                                <x-money :amount="$subtotal + $shippingCost" />
                            </dd>
                        </div>
                    </dl>

                    <a href="{{ route('checkout.create') }}" class="btn btn-primary mt-5 w-full">
                        Lanjut ke checkout
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="square" />
                        </svg>
                    </a>

                    @guest
                        <p class="mt-2.5 text-xs leading-relaxed font-medium text-zinc-500">
                            Anda akan diminta masuk atau mendaftar terlebih dahulu.
                        </p>
                    @endguest

                    <a href="{{ route('products.index') }}" class="btn mt-2 w-full">
                        Lanjut belanja
                    </a>
                </div>
            </aside>
        </div>
    @endif
@endsection
