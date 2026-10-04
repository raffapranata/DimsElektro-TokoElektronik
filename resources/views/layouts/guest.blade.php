<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Masuk') &middot; {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col items-center justify-center bg-cream px-4 py-10 font-sans text-zinc-900 antialiased">
    <a href="{{ route('home') }}" class="mb-8 flex items-center gap-3 text-xl font-bold tracking-tight text-zinc-900">
        <span class="grid size-10 place-items-center border-2 border-zinc-900 bg-amber-300 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
            <svg class="size-5 text-zinc-950" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                <path d="M13 2 4.5 13.5H11L10 22l8.5-11.5H13L13 2Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
        <span class="font-pixel text-xs">Dims<span class="text-brand-600">Elektro</span></span>
    </a>

    <div class="w-full max-w-md">
        @if (session('status'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showToast("{{ session('status') }}", 'success');
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showToast("{{ session('error') }}", 'error');
                });
            </script>
        @endif

        @include('partials.flash')

        <div class="card p-6 sm:p-8">
            @yield('content')
        </div>

        <p class="mt-6 text-center text-sm font-medium text-zinc-500">
            @yield('footer')
        </p>
    </div>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (!input || !icon) return;
            
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            
            // Toggle icon visibility
            const openPaths = icon.querySelectorAll('.icon-open');
            const closedPaths = icon.querySelectorAll('.icon-closed');
            
            openPaths.forEach(el => el.classList.toggle('hidden', isPassword));
            closedPaths.forEach(el => el.classList.toggle('hidden', !isPassword));
        }
    </script>
</body>
</html>
