@if (session('status'))
    @if (request()->routeIs('admin.*'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast("{{ session('status') }}", 'success');
            });
        </script>
    @else
        <x-alert type="success" class="mb-6">{{ session('status') }}</x-alert>
    @endif
@endif

@if (session('error'))
    @if (request()->routeIs('admin.*'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast("{{ session('error') }}", 'error');
            });
        </script>
    @else
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif
@endif

@if ($errors->any() && ! isset($hideErrorSummary))
    <x-alert type="error" class="mb-6">
        <p class="font-bold">Periksa kembali data yang Anda masukkan.</p>
        <ul class="mt-1 list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif
