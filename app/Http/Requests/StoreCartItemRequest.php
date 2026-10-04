<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCartItemRequest extends FormRequest
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
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * Availability and stock depend on application state, not on the shape of
     * the payload, so they are checked after the base rules pass.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['product_id', 'quantity'])) {
                    return;
                }

                $product = Product::query()->find($this->integer('product_id'));

                if ($product === null || ! $product->is_active) {
                    $validator->errors()->add('product_id', 'Produk tidak tersedia.');

                    return;
                }

                if ($product->stock < $this->integer('quantity')) {
                    $validator->errors()->add('quantity', 'Stok produk tidak mencukupi.');
                }
            },
        ];
    }
}
