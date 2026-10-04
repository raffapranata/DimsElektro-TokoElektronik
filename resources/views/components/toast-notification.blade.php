{{-- Retro Pixel Toast Notification with Auto-dismiss --}}
<div id="toast-container" class="fixed bottom-6 right-6 z-40 space-y-3 max-w-sm"></div>

<script>
    window.showToast = function(message, type = 'success', duration = 4000) {
        const container = document.getElementById('toast-container');
        const id = 'toast-' + Date.now();
        
        const bgColor = type === 'success' ? 'bg-brand-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600';
        const borderColor = type === 'success' ? 'border-brand-900' : type === 'error' ? 'border-red-900' : 'border-blue-900';
        
        const toast = document.createElement('div');
        toast.id = id;
        toast.className = `border-3 ${borderColor} ${bgColor} shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] rounded-sm`;
        toast.innerHTML = `
            <div class="px-5 py-4 text-sm font-medium text-white">
                <div class="flex items-start justify-between gap-4">
                    <p>${message}</p>
                    <button onclick="document.getElementById('${id}').remove()" 
                            class="flex-shrink-0 text-white hover:opacity-75 transition-opacity">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="mt-3 h-1 bg-black/20 overflow-hidden rounded-full">
                    <div class="h-full bg-white animate-shrink" style="animation-duration: ${duration}ms"></div>
                </div>
            </div>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };
</script>

<style>
    @keyframes shrink {
        from { width: 100%; }
        to { width: 0%; }
    }
    .animate-shrink {
        animation: shrink linear forwards;
    }
</style>
