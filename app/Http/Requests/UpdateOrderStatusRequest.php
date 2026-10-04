<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * The admin may update the order status, the payment status, or both in
     * one submission. At least one must be present (checked in withValidator).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
            'payment_status' => ['nullable', Rule::enum(PaymentStatus::class)],
        ];
    }

    /**
     * Require at least one of the two statuses to be present.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->status === null && $this->payment_status === null) {
                $validator->errors()->add('status', 'Pilih status pesanan atau status pembayaran.');
            }
        });
    }

    public function orderStatus(): ?OrderStatus
    {
        $value = $this->input('status');

        return $value === null ? null : OrderStatus::from($value);
    }

    public function paymentStatus(): ?PaymentStatus
    {
        $value = $this->input('payment_status');

        return $value === null ? null : PaymentStatus::from($value);
    }
}
