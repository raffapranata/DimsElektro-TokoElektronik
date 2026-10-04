@extends('layouts.admin')

@section('title', 'Pesanan')
@section('heading', 'Pesanan')

@section('content')
    @if ($orders->isEmpty())
        <x-empty-state
            title="Belum ada pesanan"
            description="Pesanan pelanggan akan muncul di sini setelah ada transaksi." />
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y-2 divide-zinc-900">
                    <thead class="bg-amber-50">
                        <tr>
                            <th scope="col" class="table-th">Nomor</th>
                            <th scope="col" class="table-th">Pelanggan</th>
                            <th scope="col" class="table-th text-center">Item</th>
                            <th scope="col" class="table-th text-right">Total</th>
                            <th scope="col" class="table-th text-center">Status</th>
                            <th scope="col" class="table-th text-center">Pembayaran</th>
                            <th scope="col" class="table-th">Tanggal</th>
                            <th scope="col" class="table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-zinc-100">
                        @foreach ($orders as $order)
                            <tr class="table-row-hover">
                                <td class="table-td font-pixel text-[8px] text-zinc-900">
                                    {{ $order->order_number }}
                                </td>
                                <td class="table-td">
                                    <p class="font-bold text-zinc-900">{{ $order->user->name }}</p>
                                    <p class="text-xs font-medium text-zinc-500">{{ $order->user->email }}</p>
                                </td>
                                <td class="table-td text-center font-medium text-zinc-600">{{ $order->items_count }}</td>
                                <td class="table-td text-right font-bold text-zinc-900">
                                    <x-money :amount="$order->total_amount" />
                                </td>
                                <td class="table-td text-center"><x-status-badge :status="$order->status" /></td>
                                <td class="table-td text-center">
                                    @if ($order->payment)
                                        <x-status-badge :status="$order->payment->status" />
                                    @else
                                        <span class="text-xs font-medium text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="table-td whitespace-nowrap font-medium text-zinc-500">
                                    <x-datetime :value="$order->created_at" />
                                </td>
                                <td class="table-td">
                                    <div class="flex justify-end">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm">
                                            Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($orders->hasPages())
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    @endif
@endsection
