<div>
    <!-- Trigger Button -->
    <div class="" wire:click="openCollectModal" title="{{ __('messages.cash_out') }}" style="cursor: pointer;"
        data-placement="top">

        <button class="btn btn-default p-1 m-0 border-0 bg-transparent">
            <img src="{{ asset('public/external/vector4471-k5i.svg') }}" alt="Cash Out Icon"
                style="width: 20px; height: 20px;" />
        </button>
        <span class="ic-txt">Add Cash</span>
    </div>

    <!-- Modal -->
    @if ($showCollectModal)
        <div class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable add-cash-modal">
                <div class="modal-content">
                    <div class="modal-header custom-modal-header">
                        <h6 class="modal-title cash-summary-text61">Add Cash</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            wire:click="$set('showCollectModal', false)"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Table -->
                         <div class="table-responsive">
                            <table class=" table table-bordered ">
                                <thead class="table-dark">
                                    <tr >
                                        <th class="text-center" style="width: 15%;" >Amount</th>
                                        <th class="text-center" style="width: 25%;" >In</th>
                                        <th class="text-center" style="width: 20%;" >Currency</th>
                                        <th class="text-center" style="width: 25%;" >Out</th>
                                        <th class="text-center" style="width: 15%;" >Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($noteDenominations as $key => $denomination)
                                        @php
                                            $inValue = $cashNotes[$key][$denomination]['in'] ?? 0;
                                            $outValue = $cashNotes[$key][$denomination]['out'] ?? 0;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ format_inr($inValue * $denomination) }}</td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center counter-add-delete-area" style="width: 100%">
                                                    <button style="width: 40%;" class="btn btn-gray rounded-start"
                                                        wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'in')">−</button>
                                                    <input class="form-control text-center rounded-0" type="text" value="{{ $inValue }}" style="width: 60px;" readonly>
                                                    <button style="width: 40%;"  class="btn btn-gray rounded-end"
                                                        wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'in')">+</button>
                                                </div>
                                            </td>
                                            <td class="text-center currency-center">{{ format_inr($denomination) }}</td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center counter-add-delete-area" style="width: 100%" >
                                                    <button style="width: 40%;"  class="btn btn-gray rounded-start"
                                                        wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'out')">−</button>
                                                    <input style="width: 60px;"  class="form-control text-center rounded-0"  type="text" value="{{ $outValue }}" readonly>
                                                    <button style="width: 40%;"  class="btn btn-gray rounded-end"
                                                        wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'out')">+</button>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ format_inr($outValue * $denomination) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-secondary fw-bold total-summary-block">
                                        <td class="total_bgc text-center">{{ format_inr($totals['totalIn']) }}</td>
                                        <td class="total_bgc text-center">{{ $totals['totalInCount'] }}</td>
                                        <td class="text-success text-center total_bgc">TOTAL</td>
                                        <td class="total_bgc text-center">{{ $totals['totalOutCount'] }}</td>
                                        <td class="total_bgc text-center">{{ format_inr($totals['totalOut']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Transaction Type & Total Amount -->
                        <div class="transaction-type-add">
                            <div class="col-12 bg-light shadow-sm">
                                <div class="row align-items-center mt-4 px-2 py-3 rounded">
                                    <div class="col-md-7 d-flex align-items-center gap-3">
                                        <label class="fw-bold mb-0 text-secondary">Transaction Type:</label>
                                        <div class="form-check form-check-inline mb-0">
                                            <input class="form-check-input custom-radio" type="radio"
                                                wire:model="transactionType" wire:click="setTransactionType('change')"
                                                name="transactionType" id="change" value="change">
                                            <label class="form-check-label text-secondary" for="change">
                                                <i class="bi bi-arrow-left-right me-1"></i> Change
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline mb-0">
                                            <input class="form-check-input custom-radio" type="radio"
                                                wire:model="transactionType" wire:click="setTransactionType('add')"
                                                name="transactionType" id="addMoney" value="add">
                                            <label class="form-check-label text-secondary" for="addMoney">
                                                <i class="bi bi-plus-circle me-1"></i> Add Money
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-5 text-end">
                                        <span class="fw-bold text-secondary">Total Amount:</span>
                                        <span class="fw-bold text-success fs-5 ms-2">{{ $this->totalCollected }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="addcashsubmit-btn">
                            <!-- Submit -->
                            @if ($this->totalCollected >= 0)
                            <button wire:click="submitCredit" class="btn pull-right rounded-pill submit-btn">
                                Submit
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
