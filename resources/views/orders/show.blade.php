@extends('layouts.app')

@section('title', 'Pesanan '.$order->order_number)

@section('content')
    <nav class="flex items-center gap-1 text-sm font-medium text-zinc-500" aria-label="Breadcrumb">
        <a href="{{ route('orders.index') }}" class="underline decoration-2 underline-offset-2 transition-colors hover:text-zinc-900">Pesanan saya</a>
        <span class="text-zinc-300">/</span>
        <span class="font-pixel text-[10px] text-zinc-900">{{ $order->order_number }}</span>
    </nav>

    <div class="mt-5 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="font-pixel text-xs text-zinc-900">{{ $order->order_number }}</h1>
            <p class="mt-2 text-sm font-medium text-zinc-500">
                Dibuat <x-datetime :value="$order->created_at" />
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-status-badge :status="$order->status" />
            @if ($order->payment)
                <x-status-badge :status="$order->payment->status" />
            @endif

            @can('cancel', $order)
                <form method="POST" action="{{ route('orders.cancel', $order) }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Batalkan pesanan</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            <div class="card overflow-hidden">
                <h2 class="border-b-2 border-zinc-900 bg-amber-300 px-5 py-3 font-pixel text-[9px] text-zinc-950">ITEM PESANAN</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y-2 divide-zinc-900">
                        <thead class="bg-white">
                            <tr>
                                <th scope="col" class="table-th">Produk</th>
                                <th scope="col" class="table-th text-right">Harga</th>
                                <th scope="col" class="table-th text-right">Jumlah</th>
                                <th scope="col" class="table-th text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-zinc-100 bg-white">
                            @foreach ($order->items as $item)
                                <tr class="table-row-hover">
                                    <td class="table-td font-bold text-zinc-900">{{ $item->product_name }}</td>
                                    <td class="table-td text-right"><x-money :amount="$item->price" /></td>
                                    <td class="table-td text-right">{{ $item->quantity }}</td>
                                    <td class="table-td text-right font-bold"><x-money :amount="$item->subtotal" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-amber-50">
                            <tr>
                                <td colspan="3" class="table-td text-right text-zinc-500">Subtotal</td>
                                <td class="table-td text-right font-bold"><x-money :amount="$order->subtotal" /></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="table-td text-right text-zinc-500">Pengiriman</td>
                                <td class="table-td text-right font-bold">
                                    @if ((float) $order->shipping_cost > 0)
                                        <x-money :amount="$order->shipping_cost" />
                                    @else
                                        <span class="text-brand-700">Gratis</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="table-td text-right font-black text-zinc-900">Total</td>
                                <td class="table-td text-right font-pixel text-[10px] text-zinc-950">
                                    <x-money :amount="$order->total_amount" />
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card p-5">
                <h2 class="font-pixel text-[10px] text-zinc-900">ALAMAT PENGIRIMAN</h2>
                <dl class="mt-4 space-y-2.5 text-sm">
                    <div class="flex gap-3">
                        <dt class="w-24 shrink-0 font-medium text-zinc-500">Penerima</dt>
                        <dd class="font-bold text-zinc-900">{{ $order->recipient_name }}</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="w-24 shrink-0 font-medium text-zinc-500">Telepon</dt>
                        <dd class="font-bold text-zinc-900">{{ $order->recipient_phone }}</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="w-24 shrink-0 font-medium text-zinc-500">Alamat</dt>
                        <dd class="text-zinc-900">{{ $order->shipping_address }}</dd>
                    </div>
                    @if ($order->notes)
                        <div class="flex gap-3">
                            <dt class="w-24 shrink-0 font-medium text-zinc-500">Catatan</dt>
                            <dd class="text-zinc-900">{{ $order->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <aside class="lg:col-span-1">
            <div class="card sticky top-36 p-5">
                <h2 class="font-pixel text-[10px] text-zinc-900">PEMBAYARAN</h2>

                @if ($order->payment === null)
                    <p class="mt-3 text-sm font-medium text-zinc-500">Belum ada data pembayaran untuk pesanan ini.</p>
                @else
                    <dl class="mt-4 space-y-2.5 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="font-medium text-zinc-500">Status</dt>
                            <dd><x-status-badge :status="$order->payment->status" /></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="font-medium text-zinc-500">Jumlah</dt>
                            <dd class="font-bold text-zinc-900"><x-money :amount="$order->payment->amount" /></dd>
                        </div>
                        @if ($order->payment->payment_type)
                            <div class="flex items-center justify-between gap-3">
                                <dt class="font-medium text-zinc-500">Metode</dt>
                                <dd class="text-zinc-900">{{ $order->payment->payment_type }}</dd>
                            </div>
                        @endif
                        @if ($order->payment->transaction_id)
                            <div class="flex items-center justify-between gap-3">
                                <dt class="font-medium text-zinc-500">Referensi</dt>
                                <dd class="font-pixel text-[8px] text-zinc-900">{{ $order->payment->transaction_id }}</dd>
                            </div>
                        @endif
                        @if ($order->payment->paid_at)
                            <div class="flex items-center justify-between gap-3">
                                <dt class="font-medium text-zinc-500">Dibayar</dt>
                                <dd class="text-zinc-900"><x-datetime :value="$order->payment->paid_at" /></dd>
                            </div>
                        @endif
                    </dl>

                    @if ($order->payment->isPending())
                        <x-alert type="info" class="mt-5">
                            Selesaikan pembayaran sesuai instruksi dari tim kami. Pesanan akan diproses
                            setelah pembayaran diverifikasi.
                        </x-alert>
                    @endif
                @endif
            </div>
        </aside>
    </div>
@endsection
