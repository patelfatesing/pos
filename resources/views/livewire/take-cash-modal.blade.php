<div>
 
     <button wire:click="openCollectModal"
        class="btn btn-primary ml-2" data-toggle="tooltip" data-placement="top"
                                    title="Add Cash">
        <i class="fa fa-money-bill"></i> 
    </button>
 
    <!-- Collect Credit Modal -->
    @if ($showCollectModal)
        <div class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Cash</h5>
                        <button type="button" class="close" wire:click="$set('showCollectModal', false)">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <table class="customtable table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    @if (empty($this->selectedSalesReturn))
                                        <th>{{ __('messages.amount') }}</th>
                                        <th class="text-center">{{ __('messages.in') }}</th>
                                    @endif
                                    <th>{{ __('messages.currency') }}</th>
                                    <th class="text-center">{{ __('messages.out') }}</th>
                                    <th class="text-center">
                                        {{ __('messages.amount') }}
                                        <button wire:click="clearCashNotes" class="btn btn-danger btn-sm">
                                            <i class="fa fa-eraser"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($noteDenominations as $key => $denomination)
                                    @php
                                        $inValue = $cashNotes[$key][$denomination]['in'] ?? 0;
                                        $outValue = $cashNotes[$key][$denomination]['out'] ?? 0;
                                    @endphp
                                    <tr>
                                        @if (empty($this->selectedSalesReturn))
                                            <td class="text-center fw-bold">
                                                {{ format_inr($inValue * $denomination) }}</td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center align-items-center gap-2">
                                                    <button class="btn btn-sm btn-danger custom-btn"
                                                        wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'in')">
                                                        -
                                                    </button>
                                                    <input type="number" class="form-control text-center"
                                                        value="{{ $inValue }}" readonly style="width: 60px;">
                                                    <button class="btn btn-sm btn-success custom-btn"
                                                        wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'in')">
                                                        +
                                                    </button>
                                                </div>
                                            </td>
                                        @endif

                                        <td class="text-center">{{ format_inr($denomination) }}</td>

                                        <td class="text-center">
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <button class="btn btn-sm btn-danger custom-btn"
                                                    wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'out')">
                                                    -
                                                </button>
                                                <input type="number" class="form-control text-center"
                                                    value="{{ $outValue }}" readonly style="width: 60px;">
                                                <button class="btn btn-sm btn-success custom-btn"
                                                    wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'out')">
                                                    +
                                                </button>
                                            </div>
                                        </td>

                                        <td class="text-center fw-bold">
                                            {{ format_inr($outValue * $denomination) }}
                                        </td>
                                    </tr>
                                @endforeach

                                <tr class="table-secondary fw-bold">
                                    @if (empty($this->selectedSalesReturn))
                                        <td class="text-center">{{ format_inr($totals['totalIn']) }}</td>
                                        <td class="text-center">{{ $totals['totalInCount'] }}</td>
                                    @endif
                                    <td class="text-center">TOTAL</td>
                                    <td class="text-center">{{ $totals['totalOutCount'] }}</td>
                                    <td class="text-center">{{ format_inr($totals['totalOut']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Radio Buttons for Add Money or Change -->
                        <div class="mb-3">
                            <label class="fw-bold">Transaction Type:</label>
                            
                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                    type="radio"
                                    wire:model="transactionType"
                                    wire:click="setTransactionType('change')"
                                    name="transactionType"
                                    id="change"
                                    value="change">
                                <label class="form-check-label" for="change">Change</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                    type="radio"
                                    wire:model="transactionType"
                                    wire:click="setTransactionType('add')"
                                    name="transactionType"
                                    id="addMoney"
                                    value="add">
                                <label class="form-check-label" for="addMoney">Add Money</label>
                            </div>

                        </div>


                        <!-- Total Collected Display -->
                        <div class="text-end mt-3">
                            <h5>
                                Total Amount:
                                <span class="badge bg-secondary">
                                    {{$this->totalCollected}}
                                  
                                </span>
                            </h5>
                        </div>
                        @if($this->totalCollected>=0)
                            <!-- Submit Button -->
                            <div class="text-right">
                                <button wire:click="submitCredit" class="btn btn-primary mt-2">
                                    Submit
                                </button>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
