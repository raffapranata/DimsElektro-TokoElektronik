@props(['amount' => 0])

{{ \Illuminate\Support\Number::currency((float) $amount, 'IDR', 'id', 0) }}
