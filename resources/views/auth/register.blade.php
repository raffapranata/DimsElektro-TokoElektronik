@extends('layouts.guest')

@section('title', 'Daftar')

@section('content')
    <p class="overline-label">// NEW GAME</p>
    <h1 class="mt-2 text-lg font-black tracking-tight text-zinc-900">Buat akun pelanggan</h1>
    <p class="mt-1 text-sm font-medium text-zinc-500">Cukup satu akun untuk seluruh riwayat pembelian Anda.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="name" class="label">Nama lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}"
                   required autofocus autocomplete="name"
                   class="input mt-1.5 @error('name') input-error @enderror">
            <x-input-error name="name" />
        </div>

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   required autocomplete="username"
                   class="input mt-1.5 @error('email') input-error @enderror">
            <x-input-error name="email" />
        </div>

        <div>
            <label for="password" class="label">Kata sandi</label>
            <div class="relative mt-1.5">
                <input id="password" name="password" type="password"
                       required autocomplete="new-password"
                       class="input w-full pr-12 @error('password') input-error @enderror">
                <button type="button"
                        onclick="togglePasswordVisibility('password', 'password-toggle-icon')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 grid size-6 place-items-center text-zinc-700 hover:text-zinc-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900"
                        aria-label="Lihat/Sembunyikan kata sandi">
                    <svg id="password-toggle-icon" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                        <path class="icon-open" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle class="icon-open" cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path class="icon-closed hidden" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke-linecap="round" stroke-linejoin="round"/>
                        <line class="icon-closed hidden" x1="1" y1="1" x2="23" y2="23" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <p class="mt-1 text-xs font-medium text-zinc-500">Minimal 8 karakter.</p>
            <x-input-error name="password" />
        </div>

        <div>
            <label for="password_confirmation" class="label">Konfirmasi kata sandi</label>
            <div class="relative mt-1.5">
                <input id="password_confirmation" name="password_confirmation" type="password"
                       required autocomplete="new-password"
                       class="input w-full pr-12 @error('password_confirmation') input-error @enderror">
                <button type="button"
                        onclick="togglePasswordVisibility('password_confirmation', 'password-confirmation-toggle-icon')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 grid size-6 place-items-center text-zinc-700 hover:text-zinc-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900"
                        aria-label="Lihat/Sembunyikan konfirmasi kata sandi">
                    <svg id="password-confirmation-toggle-icon" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                        <path class="icon-open" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle class="icon-open" cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path class="icon-closed hidden" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke-linecap="round" stroke-linejoin="round"/>
                        <line class="icon-closed hidden" x1="1" y1="1" x2="23" y2="23" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <x-input-error name="password_confirmation" />
        </div>

        <button type="submit" class="btn btn-primary w-full font-pixel text-[9px] uppercase">New game</button>
    </form>
@endsection

@section('footer')
    Sudah punya akun?
    <a href="{{ route('login') }}" class="link">Masuk di sini</a>
@endsection
