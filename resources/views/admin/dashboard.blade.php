@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card flex items-start justify-between p-5">
            <div>
                <p class="text-xs font-bold tracking-wide text-zinc-500 uppercase">Total produk</p>
                <p class="mt-2 font-pixel text-sm text-zinc-950">{{ number_format($stats['products'], 0, ',', '.') }}</p>
                <p class="mt-1.5 text-xs font-medium text-zinc-500">{{ $stats['activeProducts'] }} produk aktif</p>
            </div>
            <span class="grid size-10 shrink-0 place-items-center border-2 border-zinc-900 bg-brand-500 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <svg class="size-5 text-zinc-950" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                    <path d="M20 7H4m16 5H4m16 5H4" stroke-linecap="square" />
                </svg>
            </span>
        </div>

        <div class="card flex items-start justify-between p-5">
            <div>
                <p class="text-xs font-bold tracking-wide text-zinc-500 uppercase">Total pesanan</p>
                <p class="mt-2 font-pixel text-sm text-zinc-950">{{ number_format($stats['orders'], 0, ',', '.') }}</p>
                <p class="mt-1.5 text-xs font-medium text-zinc-500">Seluruh riwayat pesanan</p>
            </div>
            <span class="grid size-10 shrink-0 place-items-center border-2 border-zinc-900 bg-amber-300 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <svg class="size-5 text-zinc-950" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                    <path d="M6 4h12l2 4-8 12L4 8l2-4Zm-2 4h16M10 8l2 12 2-12" stroke-linecap="square" stroke-linejoin="round" />
                </svg>
            </span>
        </div>

        <div class="card flex items-start justify-between p-5">
            <div>
                <p class="text-xs font-bold tracking-wide text-zinc-500 uppercase">Menunggu bayar</p>
                <p class="mt-2 font-pixel text-sm text-amber-700">{{ number_format($stats['pendingOrders'], 0, ',', '.') }}</p>
                <p class="mt-1.5 text-xs font-medium text-zinc-500">Perlu ditindaklanjuti</p>
            </div>
            <span class="grid size-10 shrink-0 place-items-center border-2 border-zinc-900 bg-orange-400 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <svg class="size-5 text-zinc-950" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 7v5l3 3" stroke-linecap="square" />
                </svg>
            </span>
        </div>

        <div class="card flex items-start justify-between p-5">
            <div>
                <p class="text-xs font-bold tracking-wide text-zinc-500 uppercase">Selesai</p>
                <p class="mt-2 font-pixel text-sm text-brand-700">{{ number_format($stats['completedOrders'], 0, ',', '.') }}</p>
                <p class="mt-1.5 text-xs font-medium text-zinc-500">
                    Nilai <x-money :amount="$stats['completedRevenue']" />
                </p>
            </div>
            <span class="grid size-10 shrink-0 place-items-center border-2 border-zinc-900 bg-brand-600 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <svg class="size-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" aria-hidden="true">
                    <path d="m5 13 4 4L19 7" stroke-linecap="square" />
                </svg>
            </span>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="card overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b-2 border-zinc-900 bg-amber-300 px-5 py-3">
                <h2 class="font-pixel text-[9px] text-zinc-950">STOK MENIPIS</h2>
                <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="text-xs font-bold text-zinc-950 underline decoration-2 underline-offset-2">Lihat semua</a>
            </div>

            @if ($lowStockProducts->isEmpty())
                <x-empty-state
                    class="m-4 border-0 shadow-none"
                    title="Semua stok aman"
                    :description="'Tidak ada produk dengan stok '.$lowStockThreshold.' unit atau kurang.'" />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y-2 divide-zinc-900">
                        <thead class="bg-white">
                            <tr>
                                <th scope="col" class="table-th">Produk</th>
                                <th scope="col" class="table-th">Kategori</th>
                                <th scope="col" class="table-th text-right">Stok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-zinc-100 bg-white">
                            @foreach ($lowStockProducts as $product)
                                <tr class="table-row-hover">
                                    <td class="table-td">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="font-bold text-zinc-900 underline decoration-2 underline-offset-2 transition-colors hover:text-brand-700">
                                            {{ $product->name }}
                                        </a>
                                    </td>
                                    <td class="table-td">{{ $product->category->name }}</td>
                                    <td class="table-td text-right">
                                        <span @class([
                                            'badge',
                                            'bg-red-500 text-white' => $product->stock === 0,
                                            'bg-amber-300 text-zinc-950' => $product->stock > 0,
                                        ])>
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="card overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b-2 border-zinc-900 bg-amber-300 px-5 py-3">
                <h2 class="font-pixel text-[9px] text-zinc-950">PESANAN TERBARU</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-zinc-950 underline decoration-2 underline-offset-2">Lihat semua</a>
            </div>

            @if ($recentOrders->isEmpty())
                <x-empty-state class="m-4 border-0 shadow-none" title="Belum ada pesanan" />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y-2 divide-zinc-900">
                        <thead class="bg-white">
                            <tr>
                                <th scope="col" class="table-th">Nomor</th>
                                <th scope="col" class="table-th">Pelanggan</th>
                                <th scope="col" class="table-th">Status</th>
                                <th scope="col" class="table-th text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-zinc-100 bg-white">
                            @foreach ($recentOrders as $order)
                                <tr class="table-row-hover">
                                    <td class="table-td">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-pixel text-[8px] text-zinc-900 underline decoration-2 underline-offset-2 transition-colors hover:text-brand-700">
                                            {{ $order->order_number }}
                                        </a>
                                        <p class="mt-1 text-xs font-medium text-zinc-500">
                                            <x-datetime :value="$order->created_at" />
                                        </p>
                                    </td>
                                    <td class="table-td">{{ $order->user->name }}</td>
                                    <td class="table-td"><x-status-badge :status="$order->status" /></td>
                                    <td class="table-td text-right font-bold"><x-money :amount="$order->total_amount" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
