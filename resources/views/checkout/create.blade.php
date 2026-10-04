@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <p class="overline-label">// CHECKOUT</p>
    <h1 class="section-title mt-1.5">Selesaikan pesanan Anda</h1>
    <p class="mt-1 text-sm font-medium text-zinc-500">Lengkapi data pengiriman, lalu konfirmasi pesanan.</p>

    <form method="POST" action="{{ route('checkout.store') }}" class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        @csrf

        <div class="space-y-5 lg:col-span-2">
            <div class="card p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-8 shrink-0 place-items-center border-2 border-zinc-900 bg-brand-500 font-pixel text-[10px] text-zinc-950 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">1</span>
                    <h2 class="font-pixel text-[10px] text-zinc-900">ALAMAT PENGIRIMAN</h2>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="recipient_name" class="label">Nama penerima</label>
                        <input id="recipient_name" name="recipient_name" type="text"
                               value="{{ old('recipient_name', $user->name) }}" required
                               class="input mt-1.5 @error('recipient_name') input-error @enderror">
                        <x-input-error name="recipient_name" />
                    </div>

                    <div>
                        <label for="recipient_phone" class="label">Nomor telepon</label>
                        <input id="recipient_phone" name="recipient_phone" type="tel"
                               value="{{ old('recipient_phone') }}" required
                               placeholder="081234567890"
                               class="input mt-1.5 @error('recipient_phone') input-error @enderror">
                        <x-input-error name="recipient_phone" />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="shipping_address" class="label">Alamat lengkap</label>
                        <textarea id="shipping_address" name="shipping_address" rows="3" required
                                  placeholder="Jalan, nomor rumah, kelurahan, kecamatan, kota, kode pos"
                                  class="input mt-1.5 @error('shipping_address') input-error @enderror">{{ old('shipping_address') }}</textarea>
                        <x-input-error name="shipping_address" />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="label">Catatan <span class="font-medium text-zinc-400">(opsional)</span></label>
                        <textarea id="notes" name="notes" rows="2"
                                  placeholder="Patokan alamat atau instruksi pengiriman"
                                  class="input mt-1.5 @error('notes') input-error @enderror">{{ old('notes') }}</textarea>
                        <x-input-error name="notes" />
                    </div>
                </div>
            </div>

            <div class="card p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-8 shrink-0 place-items-center border-2 border-zinc-900 bg-amber-300 font-pixel text-[10px] text-zinc-950 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">2</span>
                    <h2 class="font-pixel text-[10px] text-zinc-900">METODE PEMBAYARAN</h2>
                </div>

                <p class="mt-4 text-sm leading-relaxed text-zinc-600">
                    Pembayaran akan dicatat sebagai <span class="font-bold text-zinc-900">menunggu pembayaran</span>.
                    Instruksi pembayaran tampil pada halaman detail pesanan setelah checkout selesai.
                </p>
                <p class="mt-2 text-xs font-medium text-zinc-400">
                    Integrasi Midtrans belum diaktifkan pada instalasi ini; gateway lokal digunakan sebagai gantinya.
                </p>
            </div>
        </div>

        <aside class="lg:col-span-1">
            <div class="card sticky top-36 p-5">
                <h2 class="font-pixel text-[10px] text-zinc-900">RINGKASAN</h2>

                <ul class="mt-4 divide-y-2 divide-zinc-100">
                    @foreach ($lines as $line)
                        <li class="flex items-start gap-3 py-3">
                            <x-product-thumb :product="$line['product']" size="size-12" />

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-zinc-900">{{ $line['product']->name }}</p>
                                <p class="mt-0.5 text-xs font-medium text-zinc-500">
                                    {{ $line['quantity'] }} &times; <x-money :amount="$line['product']->price" />
                                </p>
                            </div>

                            <span class="text-sm font-bold text-zinc-900">
                                <x-money :amount="$line['subtotal']" />
                            </span>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-3 space-y-2.5 border-t-2 border-zinc-900 pt-4 text-sm">
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

                <button type="submit" class="btn btn-primary mt-5 w-full font-pixel text-[9px] uppercase">
                    Buat pesanan
                </button>

                <a href="{{ route('cart.index') }}" class="btn mt-2 w-full">Kembali ke keranjang</a>
            </div>
        </aside>
    </form>
@endsection
