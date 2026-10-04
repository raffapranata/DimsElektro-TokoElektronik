<?php

namespace App\Payment;

use App\Enums\PaymentStatus;
use Carbon\CarbonInterface;

/**
 * Gateway agnostic outcome of preparing a payment.
 */
final readonly class PaymentResult
{
    /**
     * @param  array<string, mixed>  $rawResponse  Sanitised gateway payload, never secrets
     */
    public function __construct(
        public PaymentStatus $status,
        public ?string $transactionId = null,
        public ?string $paymentType = null,
        public array $rawResponse = [],
        public ?string $redirectUrl = null,
        public ?CarbonInterface $paidAt = null,
    ) {}

    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public static function pending(
        ?string $transactionId = null,
        ?string $paymentType = null,
        array $rawResponse = [],
        ?string $redirectUrl = null,
    ): self {
        return new self(
            status: PaymentStatus::Pending,
            transactionId: $transactionId,
            paymentType: $paymentType,
            rawResponse: $rawResponse,
            redirectUrl: $redirectUrl,
        );
    }

    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public static function paid(
        ?string $transactionId = null,
        ?string $paymentType = null,
        array $rawResponse = [],
    ): self {
        return new self(
            status: PaymentStatus::Paid,
            transactionId: $transactionId,
            paymentType: $paymentType,
            rawResponse: $rawResponse,
            paidAt: now(),
        );
    }

    public function isPaid(): bool
    {
        return $this->status->isPaid();
    }

    /**
     * Attributes to write onto the order's payment record.
     *
     * @return array<string, mixed>
     */
    public function toPaymentAttributes(): array
    {
        return [
            'transaction_id' => $this->transactionId,
            'payment_type' => $this->paymentType,
            'status' => $this->status,
            'paid_at' => $this->paidAt,
            'raw_response' => $this->rawResponse === [] ? null : $this->rawResponse,
        ];
    }
}
