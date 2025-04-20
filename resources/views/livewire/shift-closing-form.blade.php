<div class="card rounded-3 shadow-sm p-4">
    <h4 class="text-primary mb-4">🧾 Shift Closing - {{ $shop_name }}</h4>

    <form wire:submit.prevent="save">
        {{-- Time Range --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Start Time</label>
                <input type="datetime-local" wire:model="start_time" class="form-control bg-light border-1 rounded" />
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">End Time</label>
                <input type="datetime-local" wire:model="end_time" class="form-control bg-light border-1 rounded" />
            </div>
        </div>

        {{-- Opening Cash --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Opening Cash</label>
                <input type="number" wire:model="opening_cash" class="form-control bg-light border-1 rounded" />
            </div>
        </div>

        {{-- Sales Section --}}
        <div class="border rounded p-3 mb-3 bg-white">
            <h5 class="text-dark">💰 Sales Summary</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <input placeholder="Deshi Sales" type="number" wire:model="deshi_sales" class="form-control" />
                </div>
                <div class="col-md-4">
                    <input placeholder="Beer Sales" type="number" wire:model="beer_sales" class="form-control" />
                </div>
                <div class="col-md-4">
                    <input placeholder="English Sales" type="number" wire:model="english_sales" class="form-control" />
                </div>
            </div>
        </div>

        {{-- Payments Section --}}
        <div class="border rounded p-3 mb-3 bg-white">
            <h5 class="text-dark">💳 Payment Details</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <input placeholder="Discount" type="number" wire:model="discount" class="form-control" />
                </div>
                <div class="col-md-4">
                    <input placeholder="UPI Payment" type="number" wire:model="upi_payment" class="form-control" />
                </div>
                <div class="col-md-4">
                    <input placeholder="Withdrawal Payment" type="number" wire:model="withdrawal_payment" class="form-control" />
                </div>
            </div>
        </div>

        {{-- Cash Details --}}
        <div class="border rounded p-3 mb-3 bg-white">
            <h5 class="text-dark">💵 Cash Denominations</h5>
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Denomination</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cash as $index => $item)
                        <tr>
                            <td class="text-center fw-bold">{{ $item['denomination'] }}</td>
                            <td class="text-center">
                                <input type="number" min="0" wire:model="cash.{{ $index }}.qty" class="form-control form-control-sm text-center" />
                            </td>
                            <td class="text-center">
                                {{ $item['denomination'] * ($item['qty'] ?? 0) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Submit Button --}}
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg shadow-sm">💾 Save Shift Close</button>
        </div>
    </form>
</div>
