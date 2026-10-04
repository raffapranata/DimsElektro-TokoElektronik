<?php

namespace App\Services;

use App\Models\Order;
use RuntimeException;

/**
 * Builds the human readable order number: INV-YYYYMMDD-XXXXXX.
 *
 * The random suffix uses an alphabet without characters that are easily
 * confused when read aloud (0/O, 1/I/L), and uniqueness is confirmed against
 * the database before the number is handed out.
 */
class OrderNumberGenerator
{
    private const string ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private const int SUFFIX_LENGTH = 6;

    private const int MAX_ATTEMPTS = 8;

    public function generate(): string
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $number = sprintf('INV-%s-%s', now()->format('Ymd'), $this->randomSuffix());

            if (! Order::query()->where('order_number', $number)->exists()) {
                return $number;
            }
        }

        throw new RuntimeException('Gagal membuat nomor pesanan unik setelah beberapa percobaan.');
    }

    private function randomSuffix(): string
    {
        $suffix = '';
        $maxIndex = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < self::SUFFIX_LENGTH; $i++) {
            $suffix .= self::ALPHABET[random_int(0, $maxIndex)];
        }

        return $suffix;
    }
}
