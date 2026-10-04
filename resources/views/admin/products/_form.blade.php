@csrf

@php
    // Old input wins after a validation error, otherwise the stored JSON map is
    // expanded back into editable rows.
    $specRows = old('specs');

    if ($specRows === null) {
        $specRows = collect($product->specs ?? [])
            ->map(fn ($value, $key): array => ['key' => $key, 'value' => $value])
            ->values()
            ->all();
    }
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="label">Nama produk</label>
        <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" required
               class="input mt-1.5 @error('name') input-error @enderror">
        <x-input-error name="name" />
    </div>

    <div>
        <label for="category_id" class="label">Kategori</label>
        <select id="category_id" name="category_id" required
                class="input mt-1.5 @error('category_id') input-error @enderror">
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                        @selected((int) old('category_id', $product->category_id) === $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <x-input-error name="category_id" />
    </div>

    <div>
        <label for="brand" class="label">Merek</label>
        <input id="brand" name="brand" type="text" value="{{ old('brand', $product->brand) }}"
               list="brand-options" required
               class="input mt-1.5 @error('brand') input-error @enderror">
        <datalist id="brand-options">
            @foreach ($brands as $brand)
                <option value="{{ $brand }}"></option>
            @endforeach
        </datalist>
        <x-input-error name="brand" />
    </div>

    <div>
        <label for="price" class="label">Harga (Rp)</label>
        <input id="price" name="price" type="number" min="0" step="1" required
               value="{{ old('price', $product->price === null ? '' : (int) $product->price) }}"
               class="input mt-1.5 @error('price') input-error @enderror">
        <p class="mt-1 text-xs font-medium text-zinc-500">Tanpa titik atau koma. Contoh: 12500000</p>
        <x-input-error name="price" />
    </div>

    <div>
        <label for="stock" class="label">Stok</label>
        <input id="stock" name="stock" type="number" min="0" step="1" required
               value="{{ old('stock', $product->stock ?? 0) }}"
               class="input mt-1.5 @error('stock') input-error @enderror">
        <x-input-error name="stock" />
    </div>

    <div>
        <label for="warranty_months" class="label">Garansi (bulan)</label>
        <input id="warranty_months" name="warranty_months" type="number" min="0" step="1" required
               value="{{ old('warranty_months', $product->warranty_months ?? 0) }}"
               class="input mt-1.5 @error('warranty_months') input-error @enderror">
        <x-input-error name="warranty_months" />
    </div>

    <div class="flex items-end pb-2">
        <label class="flex items-center gap-2 text-sm font-medium text-zinc-700">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $product->is_active ?? true))
                   class="checkbox">
            Tampilkan di katalog
        </label>
    </div>
</div>

<fieldset class="mt-8 border-t-2 border-zinc-900 pt-6">
    <legend class="font-pixel text-[10px] text-zinc-900">SPESIFIKASI TEKNIS</legend>
    <p class="mt-2 text-xs font-medium text-zinc-500">
        Isi bebas sesuai jenis produk, misalnya Processor dan RAM untuk laptop, atau Ukuran dan
        Resolusi untuk monitor.
    </p>

    <div id="specs-container" class="mt-3 space-y-2" data-next-index="{{ count($specRows) }}">
        @foreach ($specRows as $index => $row)
            <div class="flex items-center gap-2" data-spec-row>
                <input name="specs[{{ $index }}][key]" type="text" value="{{ $row['key'] ?? '' }}"
                       placeholder="Nama spesifikasi"
                       class="input @error('specs.'.$index.'.key') input-error @enderror">
                <input name="specs[{{ $index }}][value]" type="text" value="{{ $row['value'] ?? '' }}"
                       placeholder="Nilai"
                       class="input @error('specs.'.$index.'.value') input-error @enderror">
                <button type="button" class="btn btn-danger btn-sm spec-remove">Hapus</button>
            </div>
        @endforeach
    </div>

    <div class="mt-3">
        <button type="button" id="add-spec" class="btn btn-sm font-pixel text-[8px] uppercase">+ Spesifikasi</button>
    </div>

    <x-input-error name="specs" />
</fieldset>

<template id="spec-row-template">
    <div class="flex items-center gap-2" data-spec-row>
        <input name="specs[__INDEX__][key]" type="text" placeholder="Nama spesifikasi" class="input">
        <input name="specs[__INDEX__][value]" type="text" placeholder="Nilai" class="input">
        <button type="button" class="btn btn-danger btn-sm spec-remove">Hapus</button>
    </div>
</template>

<div class="mt-8 border-t-2 border-zinc-900 pt-6">
    <span class="font-pixel text-[10px] text-zinc-900">GAMBAR PRODUK</span>

    @if ($product->imageUrl())
        <div class="mt-3 flex flex-wrap items-center gap-4">
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                 class="size-20 border-2 border-zinc-900 object-cover shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]" style="image-rendering: pixelated;">

            <label class="flex items-center gap-2 text-sm font-medium text-zinc-600">
                <input type="checkbox" name="remove_image" value="1" class="checkbox">
                Hapus gambar saat ini
            </label>
        </div>
    @endif

    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
           class="input mt-3 bg-white py-1.5 text-xs file:mr-3 file:border-r-2 file:border-zinc-900 file:bg-amber-300 file:px-3 file:py-1.5 file:font-bold file:text-zinc-950 hover:file:bg-amber-200 @error('image') input-error @enderror">
    <p class="mt-1 text-xs font-medium text-zinc-500">
        JPG, PNG, atau WebP. Maksimal 2 MB. Nama berkas akan diganti otomatis.
    </p>
    <x-input-error name="image" />
</div>

@push('scripts')
    <script>
        (() => {
            const container = document.getElementById('specs-container');
            const template = document.getElementById('spec-row-template');
            const addButton = document.getElementById('add-spec');

            if (!container || !template || !addButton) {
                return;
            }

            let nextIndex = Number(container.dataset.nextIndex || 0);

            addButton.addEventListener('click', () => {
                container.insertAdjacentHTML(
                    'beforeend',
                    template.innerHTML.replaceAll('__INDEX__', String(nextIndex)),
                );

                nextIndex += 1;
            });

            container.addEventListener('click', (event) => {
                const removeButton = event.target.closest('.spec-remove');

                if (removeButton) {
                    removeButton.closest('[data-spec-row]').remove();
                }
            });
        })();
    </script>
@endpush
