<div>
    <div class="mb-2">
        <label>Tendered Amount</label>
        <input type="number" wire:model.lazy="cashAmount" class="form-control w-25 d-inline-block">
        <span class="ms-2 fw-bold text-success">Change: {{ format_inr($cashPayChangeAmt) }}</span>
    </div>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>{{ __('messages.amount') }}</th>
                @if(empty($this->selectedSalesReturn))
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
                    $rowAmount = ($inValue - $outValue) * $denomination;
                @endphp
                <tr>
                    <td class="text-center fw-bold">{{ format_inr($rowAmount) }}</td>

                    @if(empty($this->selectedSalesReturn))
                    <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            <button class="btn btn-sm btn-danger"
                                wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'in')">
                                <i class="fa fa-minus"></i>
                            </button>
                            <input type="number" class="form-control form-control-sm text-center" value="{{ $inValue }}" readonly style="width: 60px;">
                            <button class="btn btn-sm btn-success"
                                wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'in')">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </td>
                    @endif

                    <td class="text-center">{{ $denomination }}</td>

                    <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            <button class="btn btn-sm btn-danger"
                                wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'out')">
                                <i class="fa fa-minus"></i>
                            </button>
                            <input type="number" class="form-control form-control-sm text-center" value="{{ $outValue }}" readonly style="width: 60px;">
                            <button class="btn btn-sm btn-success"
                                wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'out')">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </td>

                    <td class="text-center fw-bold">{{ format_inr($rowAmount) }}</td>
                </tr>
            @endforeach

            <tr class="table-secondary fw-bold">
                <td class="text-center total_bgc">{{ format_inr($totals['totalIn']) }}</td>
                <td class="text-center total_bgc">{{ format_inr($totals['totalIn']) }}</td>
                @if(empty($this->selectedSalesReturn))
                    <td class="text-center total_bgc">-</td>
                @endif
                <td class="text-center total_bgc">{{ format_inr($totals['totalOut']) }}</td>
                <td class="text-center total_bgc">{{ format_inr($totals['totalAmount']) }}</td>
            </tr>
        </tbody>
    </table>
    
</div>
