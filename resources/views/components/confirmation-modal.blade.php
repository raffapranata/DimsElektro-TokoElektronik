{{-- Retro Pixel Confirmation Modal --}}
<div id="confirmation-modal"
     x-data="{ open: false, title: '', message: '', confirmText: 'Hapus', cancelText: 'Batal', formAction: '', formMethod: 'POST' }"
     x-show="open"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
     style="display: none;">

    <!-- Modal Card -->
    <div class="w-full max-w-sm border-4 border-zinc-900 bg-cream shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]"
         @keydown.escape="open = false">

        <!-- Header -->
        <div class="border-b-4 border-zinc-900 bg-amber-300 px-6 py-4">
            <h2 class="font-pixel text-[10px] text-zinc-900 uppercase tracking-wide" x-text="title"></h2>
        </div>

        <!-- Body -->
        <div class="px-6 py-5">
            <p class="text-sm font-medium leading-relaxed text-zinc-700" x-text="message"></p>
        </div>

        <!-- Footer -->
        <div class="border-t-4 border-zinc-900 bg-amber-50 px-6 py-4 flex items-center justify-end gap-3">
            <button type="button"
                    @click="open = false"
                    class="btn border-2 border-zinc-900 bg-white px-6 py-2 font-pixel text-[9px] uppercase text-zinc-900 hover:bg-zinc-100">
                <span x-text="cancelText"></span>
            </button>

            <form id="confirmation-form" :action="formAction" :method="formMethod === 'DELETE' ? 'POST' : 'POST'" class="inline">
                @csrf
                <template x-if="formMethod === 'DELETE'">
                    <input type="hidden" name="_method" value="DELETE">
                </template>
                <template x-if="formMethod === 'PATCH'">
                    <input type="hidden" name="_method" value="PATCH">
                </template>

                <button type="submit"
                        class="btn btn-danger border-2 border-red-900 bg-red-600 px-6 py-2 font-pixel text-[9px] uppercase text-white hover:bg-red-700">
                    <span x-text="confirmText"></span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    window.confirmAction = function(config) {
        const modal = document.getElementById('confirmation-modal');
        const data = modal.__x.$data;
        data.open = true;
        data.title = config.title || 'Konfirmasi';
        data.message = config.message || 'Apakah Anda yakin?';
        data.confirmText = config.confirmText || 'Hapus';
        data.cancelText = config.cancelText || 'Batal';
        data.formAction = config.formAction;
        data.formMethod = config.formMethod || 'POST';
    };
</script>
