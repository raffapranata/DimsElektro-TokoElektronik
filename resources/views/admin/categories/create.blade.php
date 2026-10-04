@extends('layouts.admin')

@section('title', 'Tambah Kategori')
@section('heading', 'Tambah Kategori')

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="card p-6">
            @include('admin.categories._form')

            <div class="mt-6 flex items-center gap-2 border-t-2 border-zinc-900 pt-5">
                <button type="submit" class="btn btn-primary font-pixel text-[9px] uppercase">Simpan</button>
                <a href="{{ route('admin.categories.index') }}" class="btn">Batal</a>
            </div>
        </form>
    </div>
@endsection
