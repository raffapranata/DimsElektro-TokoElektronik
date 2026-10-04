<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCartItemRequest extends FormRequest
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
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('quantity')) {
                    return;
                }

                $product = $this->route('product');

                if (! $product instanceof Product) {
                    return;
                }

                if ($product->stock < $this->integer('quantity')) {
                    $validator->errors()->add(
                        'quantity',
                        sprintf('Stok produk tidak mencukupi. Tersedia %d unit.', $product->stock),
                    );
                }
            },
        ];
    }
}
