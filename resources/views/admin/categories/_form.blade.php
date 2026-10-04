@csrf

<div>
    <label for="name" class="label">Nama kategori</label>
    <input id="name" name="name" type="text" value="{{ old('name', $category->name) }}" required
           class="input mt-1.5 @error('name') input-error @enderror">
    <x-input-error name="name" />
</div>

<div class="mt-4">
    <label for="description" class="label">Deskripsi <span class="font-medium text-zinc-400">(opsional)</span></label>
    <textarea id="description" name="description" rows="3"
              class="input mt-1.5 @error('description') input-error @enderror">{{ old('description', $category->description) }}</textarea>
    <x-input-error name="description" />
</div>

<p class="mt-4 border-2 border-zinc-900 bg-amber-50 px-3 py-2 text-xs font-medium text-zinc-600 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
    Slug dibuat otomatis dari nama kategori dan dijamin unik.
</p>
