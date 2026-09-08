<div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle hold-transactions-table mb-0" style="table-layout: fixed; width: 100%;">
            <thead class="table-info">
                <tr>
                    <th style="width: 20%;" class="text-start ps-3">Sales ID</th>
                    <th style="width: 20%;" class="text-start">Customer Name</th>
                    <th style="width: 20%;" class="text-center">Date</th>
                    <th style="width: 10%;" class="text-center">Quantity</th>
                    <th style="width: 10%;" class="text-end">Amount</th>
                    <th style="width: 20%;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @if (count($holdTransactions) == 0)
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No Hold Transactions Found</td>
                    </tr>
                @else
                    @foreach ($holdTransactions as $sid => $transaction)
                        @php
                            $sumqty = 0;
                            foreach ($transaction->items as $key => $item) {
                                $sumqty += $item['quantity'];
                            }
                        @endphp
                        <tr>
                            <td class="text-start ps-3 text-nowrap fw-medium">
                                {{ $transaction->invoice_number }}
                            </td>

                            <td class="text-start text-truncate">
                                @if (auth()->user()->hasRole('warehouse'))
                                    {{ !empty($transaction->partyUser) ? $transaction->partyUser->first_name : 'N/A' }}
                                @else
                                    {{ !empty($transaction->commissionUser) ? $transaction->commissionUser->first_name : 'N/A' }}
                                @endif
                            </td>

                            <td class="text-center text-nowrap">
                                {{ $transaction->hold_date }}
                            </td>

                            <td class="text-center fw-semibold">
                                {{ $sumqty }}
                            </td>

                            <td class="text-end fw-semibold text-nowrap">
                                ₹{{ number_format((float) ($transaction->total ?? 0), 2) }}
                            </td>

                            <td class="text-end">
                                <div class="d-inline-flex align-items-center justify-content-center gap-2 action-buttons-block">
                                    <button
                                        type="button"
                                        wire:click="resumeTransaction('{{ $transaction->id }}', '{{ $transaction->commission_user_id }}', '{{ $transaction->party_user_id }}')"
                                        class="btn btn-success btn-sm px-3 rounded-pill btn-resume-hold">
                                        Resume
                                    </button>
                                    <button 
                                        type="button"
                                        class="btn btn-light btn-sm border p-1 pdf-view btn-pdf-hold"
                                        wire:click="printInvoice('{{ $transaction->id }}')" 
                                        title="Print PDF">
                                        <img src="{{ asset('assets/images/sidebar-imgs/pdf-ic.svg') }}" alt="PDF" style="height: 20px; width: 20px;">
                                    </button>
                                    <button 
                                        type="button"
                                        class="btn btn-light btn-sm text-danger border-0 p-1 remove-item btn-delete-hold"
                                        onclick="confirmDelete({{ $transaction->id }})" 
                                        title="Delete">
                                        <i class="fa fa-trash-o fs-5" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmDelete(transactionId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('deleteTransaction', transactionId);
                document.getElementById('holdTransactionsModal').style.display = 'none';
                $('.modal-backdrop.show').remove();
            }
        });
    }
</script>