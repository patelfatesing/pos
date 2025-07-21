<div>
    <button wire:click="toggleFullscreen" id="fullscreen-btn" class="btn">
        @if ($isFullscreen)
            <img src="{{ asset('public/external/expand14471-lu4b.svg') }}" wire:click="toggleFullscreen" alt="expand14471"
                class="main-screen-expand1" />
        @else
            <img src="{{ asset('public/external/expand14471-lu4b.svg') }}" wire:click="toggleFullscreen" alt="expand14471"
                class="main-screen-expand1" />
        @endif
    </button>

</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Livewire.on('toggleFullscreen', () => {
            let elem = document.documentElement;

            if (!document.fullscreenElement) {
                elem.requestFullscreen?.() || elem.webkitRequestFullscreen?.() || elem
                    .msRequestFullscreen?.();
            } else {
                document.exitFullscreen?.() || document.webkitExitFullscreen?.() || document
                    .msExitFullscreen?.();
            }
        });
    });
</script>
