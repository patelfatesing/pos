<div class="modal fade no-print" id="closeShiftModal" tabindex="-1" aria-labelledby="closeShiftModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-sm rounded-4 border-0">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-semibold" id="closeShiftModalLabel">
                    <i class="bi bi-camera-video me-2"></i>Shift Closing - BR SHOP
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <div class="modal-body px-4 py-4">
                <form wire:submit.prevent="save">
                    {{-- Time Range --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <h5 class="">CURRENT REGISTER</h5>
                        </div>
                        <div class="col-md-6">

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Start Time</label>
                                    <input type="datetime-local" wire:model="start_time" class="form-control bg-light border-1 rounded" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">End Time</label>
                                    <input type="datetime-local" wire:model="end_time" class="form-control bg-light border-1 rounded" />
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">

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
                        </div>
                        <div class="col-md-6">
                            
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

                        </div>
                        
                        
                    </div>
                    <div class="row">
                        <div class="col-md-6">

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
                        </div>
                        <div class="col-md-6">

                            {{-- Cash Summary --}}
                            <div class="border rounded p-3 mb-3 bg-white">
                                <h5 class="text-dark">💵 Cash Summary</h5>
                                <table class="table table-bordered table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">Description</th>
                                            <th class="text-center">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center fw-bold">Opening Cash</td>
                                            <td class="text-center"><input type="number" wire:model="opening_cash" class="form-control bg-light border-1 rounded" /></td>
                                        </tr>
                                        <tr>
                                            <td class="text-center fw-bold">Total Sales</td>
                                            <td class="text-center">{{ @$total_sales }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center fw-bold">Total Payments</td>
                                            <td class="text-center">{{ @$total_payments }}</td>
                                        </tr>
                                        
                                    </tbody>
                                </table>

                                {{-- Submit Button --}}
                                <div class="d-grid text-right">
                                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">💾 Save Shift Close</button>
                                </div>
                            </div>

                        </div>
                    </div>
                
                </form>
            </div>
        </div>
    </div>
</div>
