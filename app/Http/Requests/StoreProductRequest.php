<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Upper bound on specification rows, so an oversized payload cannot turn
     * into an oversized JSON column.
     */
    public const int MAX_SPECIFICATIONS = 30;

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * The slug is never accepted from input: it is derived from the name and
     * kept unique by the model.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'brand' => ['required', 'string', 'min:2', 'max:80'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'warranty_months' => ['required', 'integer', 'min:0', 'max:1200'],
            'is_active' => ['nullable', 'boolean'],
            'specs' => ['nullable', 'array', 'max:'.self::MAX_SPECIFICATIONS],
            'specs.*.key' => ['required', 'string', 'max:60', 'distinct'],
            'specs.*.value' => ['required', 'string', 'max:255'],
            // "mimes" inspects the file contents, not the client supplied name or
            // extension. SVG is deliberately excluded: it can carry scripts.
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:max_width=6000,max_height=6000',
            ],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'price.min' => 'Harga tidak boleh negatif.',
            'specs.max' => 'Spesifikasi maksimal '.self::MAX_SPECIFICATIONS.' baris.',
            'specs.*.key.distinct' => 'Nama spesifikasi tidak boleh sama.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
            'image.dimensions' => 'Dimensi gambar maksimal 6000 x 6000 piksel.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'brand' => is_string($this->input('brand')) ? trim($this->input('brand')) : $this->input('brand'),
            'is_active' => $this->boolean('is_active'),
            'remove_image' => $this->boolean('remove_image'),
        ]);
    }

    /**
     * Turn the submitted key/value rows into the JSON map stored on the product.
     *
     * @return array<string, string>|null
     */
    public function specifications(): ?array
    {
        $rows = $this->validated('specs');

        if (! is_array($rows) || $rows === []) {
            return null;
        }

        $specifications = [];

        foreach ($rows as $row) {
            $key = trim((string) ($row['key'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));

            if ($key === '') {
                continue;
            }

            $specifications[$key] = $value;
        }

        return $specifications === [] ? null : $specifications;
    }

    /**
     * Attributes common to create and update, ready for the model.
     *
     * @return array<string, mixed>
     */
    public function productAttributes(): array
    {
        return [
            ...$this->safe()->only(['name', 'category_id', 'brand', 'price', 'stock', 'warranty_months', 'is_active']),
            'specs' => $this->specifications(),
        ];
    }
}
