@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
    <div class="flex flex-wrap items-baseline justify-between gap-2">
        <div>
            <p class="overline-label">// RIWAYAT</p>
            <h1 class="section-title mt-1.5">Pesanan saya</h1>
        </div>
        <p class="text-sm font-medium text-zinc-500">{{ $orders->total() }} pesanan</p>
    </div>

    @if ($orders->isEmpty())
        <x-empty-state
            class="mt-6"
            title="Belum ada petualangan"
            description="Pesanan yang Anda buat akan tercatat di halaman ini.">
            <x-slot:action>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Mulai belanja</a>
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="mt-6 space-y-4">
            @foreach ($orders as $order)
                <article class="card card-hover p-4 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-pixel text-[10px] text-zinc-900">{{ $order->order_number }}</p>
                            <p class="mt-1.5 text-xs font-medium text-zinc-500">
                                <x-datetime :value="$order->created_at" />
                                &middot; {{ $order->items_count }} item
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-status-badge :status="$order->status" />
                            @if ($order->payment)
                                <x-status-badge :status="$order->payment->status" />
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-end justify-between gap-3 border-t-2 border-zinc-900 pt-4">
                        <div>
                            <p class="text-xs font-medium text-zinc-500">Total pembayaran</p>
                            <p class="mt-0.5 font-pixel text-xs text-zinc-950">
                                <x-money :amount="$order->total_amount" />
                            </p>
                        </div>

                        <a href="{{ route('orders.show', $order) }}" class="btn btn-sm">
                            Lihat detail
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                <path d="M9 6l6 6-6 6" stroke-linecap="square" stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($orders->hasPages())
            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @endif
    @endif
@endsection
