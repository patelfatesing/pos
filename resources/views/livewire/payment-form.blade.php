<div class="row">

    <div class="col-md-4">
        <input type="text" id="actualCash" class="border rounded w-full p-2 bg-gray-100" value="{{ $newtotalAmount }}" >
        <label class="block font-medium">Cash:</label>
        <input type="number" id="cashAmount" step="0.01" wire:model.lazy="cash" class="border rounded w-full p-2" min="0" max="{{ $total }}">
    </div>

    <div class="col-md-4">
        <label class="block font-medium">UPI:</label>
        <input type="number" id="onlineAmount" step="0.01" wire:model.lazy="upi" class="border rounded w-full p-2" min="0" max="{{ $total }}">
    </div>
</div>