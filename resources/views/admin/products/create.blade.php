@extends('layouts.admin')

@section('title', 'Tambah Produk')
@section('heading', 'Tambah Produk')

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="card p-6">
            @include('admin.products._form')

            <div class="mt-6 flex items-center gap-2 border-t-2 border-zinc-900 pt-5">
                <button type="submit" class="btn btn-primary font-pixel text-[9px] uppercase">Simpan</button>
                <a href="{{ route('admin.products.index') }}" class="btn">Batal</a>
            </div>
        </form>
    </div>
@endsection
