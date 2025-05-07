<div wire:poll.60s="checkTime">
    <button type="button" class="btn btn-primary ml-2" data-toggle="modal"
                                data-target="#closeShiftModal" data-toggle="tooltip" data-placement="top"
                                title="Close Shift" {{ $buttonEnabled ? '' : 'disabled' }}>
                                <i class="fas fa-door-closed"></i>
                            </button>
    {{-- <button {{ $buttonEnabled ? '' : 'disabled' }}>Take Action</button>

    @if (!$buttonEnabled)
        <p class="text-sm text-gray-500">Button will be enabled 10 minutes before the end time.</p>
    @endif --}}
</div>
