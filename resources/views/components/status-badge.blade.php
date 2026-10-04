@props(['status'])

@php
    use App\Enums\OrderStatus;
    use App\Enums\PaymentStatus;

    $colours = match (true) {
        $status instanceof OrderStatus => match ($status) {
            OrderStatus::Pending => 'bg-amber-300 text-zinc-950',
            OrderStatus::Processing => 'bg-orange-200 text-orange-950',
            OrderStatus::Shipped => 'bg-brand-400 text-zinc-950',
            OrderStatus::Completed => 'bg-brand-600 text-white',
            OrderStatus::Cancelled => 'bg-zinc-200 text-zinc-600',
        },
        $status instanceof PaymentStatus => match ($status) {
            PaymentStatus::Pending => 'bg-amber-300 text-zinc-950',
            PaymentStatus::Paid => 'bg-brand-600 text-white',
            PaymentStatus::Failed => 'bg-red-500 text-white',
            PaymentStatus::Expired => 'bg-orange-200 text-orange-950',
            PaymentStatus::Cancelled => 'bg-zinc-200 text-zinc-600',
            PaymentStatus::Refunded => 'bg-teal-200 text-teal-950',
        },
        default => 'bg-zinc-100 text-zinc-700',
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge '.$colours]) }}>{{ $status->label() }}</span>
