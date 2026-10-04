@php
    /** @var \App\Models\Order $order */
@endphp

<div class="card p-5">
    <h3 class="font-pixel text-[10px] text-zinc-900 uppercase">Perbarui status</h3>
    <p class="mt-2 text-xs leading-relaxed font-medium text-zinc-500">
        Perubahan status mengikuti alur pesanan: perubahan yang tidak sah akan ditolak dan stok
        dikembalikan otomatis saat pesanan dibatalkan.
    </p>

    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-4 space-y-3.5">
        @csrf
        @method('PATCH')

        <div>
            <label for="order-status" class="mb-1.5 block text-xs font-bold text-zinc-700 uppercase">Status pesanan</label>
            <select id="order-status" name="status" class="input">
                <option value="">-- Tetap --</option>
                @foreach (\App\Enums\OrderStatus::cases() as $statusOption)
                    <option value="{{ $statusOption->value }}"
                            @selected($order->status === $statusOption)
                            @disabled(! $order->status->canTransitionTo($statusOption) && $order->status !== $statusOption)>
                        {{ $statusOption->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        @if ($order->payment)
            <div>
                <label for="payment-status" class="mb-1.5 block text-xs font-bold text-zinc-700 uppercase">Status pembayaran</label>
                <select id="payment-status" name="payment_status" class="input">
                    <option value="">-- Tetap --</option>
                    @foreach (\App\Enums\PaymentStatus::cases() as $statusOption)
                        <option value="{{ $statusOption->value }}"
                                @selected($order->payment->status === $statusOption)
                                @disabled(! $order->payment->status->canTransitionTo($statusOption) && $order->payment->status !== $statusOption)>
                            {{ $statusOption->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <button type="submit" class="btn btn-primary w-full font-pixel text-[9px] uppercase">Simpan</button>
    </form>
</div>
