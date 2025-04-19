<div class="card-body">
    <h5 class="card-title">Cart Summary</h5>
    <div class="row mb-3">
        <div class="col-md-4">
            <p>Due Amount:</p>
            <input type="number" wire:model="cashAmount" class="form-control" placeholder="Enter due amount">
        </div>
        <div class="col-md-4">
            <p>Tendered:</p>
            <input type="number" wire:model="tenderedAmount" class="form-control" placeholder="Enter tendered amount">
        </div>
        <div class="col-md-4">
            <p>Change:</p>
            <input type="number" value="{{ $changeAmount }}" class="form-control" readonly>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h4>Cash Note Breakdown</h4>
            <p>Enter note count for each denomination:</p>

            @foreach ([2000, 500, 200, 100] as $note)
                <div class="mb-2">
                    <label for="note_{{ $note }}">{{ $note }} x </label>
                    <input type="number" min="0" wire:model="noteDenominations.{{ $note }}" id="note_{{ $note }}">
                    <span> = {{ $noteDenominations[$note] * $note }}</span>
                </div>
            @endforeach

            <hr>

            <div>
                <h5>Total Breakdown:</h5>
                <ul>
                    @foreach ($totalBreakdown as $note => $amount)
                        <li>{{ $note }} x {{ $noteDenominations[$note] }} = {{ $amount }}</li>
                    @endforeach
                </ul>

                <p><strong>Remaining: </strong>
                    <input type="text" value="{{ $remainingAmount }}" readonly class="form-control w-25 d-inline" />
                </p>
            </div>
        </div>
    </div>

    @if ($commissionAmount > 0)
        <div class="d-flex justify-content-between mb-2">
            <strong>Commission Deduction</strong>
            <span>- ₹{{ number_format($commissionAmount, 2) }}</span>
        </div>
    @endif

    @if ($partyAmount > 0)
        <div class="d-flex justify-content-between mb-2">
            <strong>Point Deduction</strong>
            <span>- ₹{{ number_format($partyAmount, 2) }}</span>
        </div>
    @endif
</div>
