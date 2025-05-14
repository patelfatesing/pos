<div wire:poll.60s="checkTime">

    @if ($buttonEnabled || $testing)
        @livewire('shift-close-modal')
    @else
        <button type="button" class="btn btn-primary ml-2" title="Close Shift" disabled>
            <i class="fas fa-door-closed"></i>
        </button>
    @endif

</div>
