<div>
    <div>
        <h4>Cash Note Breakdown</h4>
        <p>Enter note count for each denomination:</p>
        <div>
            <label for="500">500 x </label>
            <input type="number" wire:model="noteDenominations.500" id="500" min="0" wire:keyup="calculateBreakdown">
            <span> = {{ $noteDenominations[500] * 500 }}</span>
        </div>
        <div>
            <label for="2000">2000 x </label>
            <input type="number" wire:model="noteDenominations.2000" id="2000" min="0" wire:keyup="calculateBreakdown">
            <span> = {{ $noteDenominations[2000] * 2000 }}</span>
        </div>
        <div>
            <label for="200">200 x </label>
            <input type="number" wire:model="noteDenominations.200" id="200" min="0" wire:keyup="calculateBreakdown">
            <span> = {{ $noteDenominations[200] * 200 }}</span>
        </div>
        <div>
            <label for="100">100 x </label>
            <input type="number" wire:model="noteDenominations.100" id="100" min="0" wire:keyup="calculateBreakdown">
            <span> = {{ $noteDenominations[100] * 100 }}</span>
        </div>

        <hr>

        <div>
            <h5>Total Breakdown:</h5>
            <ul>
                @foreach($totalBreakdown as $note => $amount)
                    <li>{{ $note }} x {{ $noteDenominations[$note] }} = {{ $amount }}</li>
                @endforeach
            </ul>

            <p><strong>Remaining: </strong>{{ $remainingAmount }}</p>
        </div>
    </div>
</div>
