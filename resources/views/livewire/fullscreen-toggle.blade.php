<div>
    <button wire:click="toggleFullscreen" id="fullscreen-btn" class="btn btn-primary ml-2">
        @if ($isFullscreen)
            <i class="fas fa-compress"></i> {{-- Exit Fullscreen Icon --}}
        @else
            <i class="fas fa-expand"></i> {{-- Enter Fullscreen Icon --}}
        @endif
    </button>
  
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Livewire.on('toggleFullscreen', () => {
            let elem = document.documentElement;

            if (!document.fullscreenElement) {
                elem.requestFullscreen?.() || elem.webkitRequestFullscreen?.() || elem.msRequestFullscreen?.();
            } else {
                document.exitFullscreen?.() || document.webkitExitFullscreen?.() || document.msExitFullscreen?.();
            }
        });
    });
</script>
