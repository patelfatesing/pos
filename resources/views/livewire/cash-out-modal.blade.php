<div>
    <!-- Trigger Button -->
    <div class="" wire:click="cashOutModal" title="{{ __('messages.cash_out') }}" style="cursor: pointer;"
        data-placement="top">

        <button class="btn btn-default p-1 m-0 border-0 bg-transparent">
            <img src="{{ asset('public/external/vector4471-k5i.svg') }}" alt="Cash Out Icon"
                style="width: 20px; height: 20px;" />
        </button>
        <span class="ic-txt">Add Cash</span>
    </div>

    <div wire:ignore.self class="modal fade" id="cashout" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable modal-md modal-dialog-centered">
            <div class="modal-content shadow-sm rounded-4 border-0">

                {{-- HEADER --}}
                <div class="modal-header custom-modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-camera-video me-2"></i>Withdraw Cash Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-3">

                    {{-- SUCCESS --}}
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="withdraw">

                        <div class="card rounded-2xl cashwithdraw-table p-2">

                            {{-- ✅ NOTE TABLE --}}
                            @if ($inOutStatus)
                                <div class="table-responsive">
                                    <table class="table table-bordered product-table">
                                        <thead class="table-info">
                                            <tr>
                                                <th class="text-center">Currency</th>
                                                <th class="text-center">Notes</th>
                                                <th class="text-center">Amount</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($noteDenominations as $key => $denomination)
                                                <tr class="text-center">
                                                    <td>{{ $denomination }} x</td>

                                                    <td>
                                                        <div class="d-flex align-items-center">

                                                            <button type="button" class="btn btn-gray"
                                                                wire:click="updateNote('{{ $key }}', {{ $denomination }}, -1)">−</button>

                                                            <span class="form-control text-center mx-1">
                                                                {{ $notes[$key][$denomination] ?? 0 }}
                                                            </span>

                                                            <button type="button" class="btn btn-gray"
                                                                wire:click="updateNote('{{ $key }}', {{ $denomination }}, 1)">+</button>

                                                        </div>
                                                    </td>

                                                    <td>
                                                        ₹{{ ($notes[$key][$denomination] ?? 0) * $denomination }}
                                                    </td>
                                                </tr>
                                            @endforeach

                                            {{-- TOTAL --}}
                                            <tr class="table-success fw-bold">
                                                <td colspan="2">Total Amount</td>
                                                <td class="text-center">
                                                    ₹{{ $this->getTotal() }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                {{-- MANUAL INPUT --}}
                                <input type="number" wire:model.defer="amount" class="form-control mb-2"
                                    placeholder="Enter amount">

                                @error('amount')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            @endif

                            {{-- CATEGORY --}}
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label class="form-label">Select Reason</label>

                                    <select wire:model="narration" class="form-control">
                                        <option value="">-- Select Reason --</option>

                                        @foreach ($narrations as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>

                                    @error('narration')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- NOTES --}}
                                <div class="col-md-6">
                                    <label class="form-label">Notes</label>

                                    <textarea wire:model.defer="withdraw_notes" class="form-control" style="height: 40px;"></textarea>
                                </div>
                            </div>

                            {{-- SUBMIT --}}
                            <div class="text-end mt-3">
                                <button type="submit" class="btn rounded-pill submit-btn" wire:loading.attr="disabled">

                                    <span wire:loading.remove>
                                        <i class="fas fa-paper-plane me-1"></i>Submit
                                    </span>

                                    <span wire:loading>Processing...</span>

                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
