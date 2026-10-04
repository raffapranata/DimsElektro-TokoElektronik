<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'min:3', 'max:100'],
            'recipient_phone' => ['required', 'string', 'min:9', 'max:25', 'regex:/^[0-9+()\-\s]+$/'],
            'shipping_address' => ['required', 'string', 'min:10', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'recipient_phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda +, tanda -, dan tanda kurung.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $trimmed = $this->only(['recipient_name', 'recipient_phone', 'shipping_address', 'notes']);

        foreach ($trimmed as $key => $value) {
            if (is_string($value)) {
                $trimmed[$key] = trim($value);
            }
        }

        $this->merge($trimmed);
    }

    /**
     * Separators are allowed for readability, but the underlying number must
     * still look like an Indonesian phone number.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('recipient_phone')) {
                    return;
                }

                $digits = preg_replace('/\D/', '', (string) $this->input('recipient_phone'));

                if (preg_match('/^(0|62)\d{8,13}$/', (string) $digits) !== 1) {
                    $validator->errors()->add(
                        'recipient_phone',
                        'Nomor telepon tidak valid. Contoh: 081234567890.',
                    );
                }
            },
        ];
    }
}
