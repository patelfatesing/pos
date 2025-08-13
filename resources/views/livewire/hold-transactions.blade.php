<div>
    <table class="table table-bordered hold-transactions-table">
        <thead class="table-info">
            <tr>
                <th class="text-start">Sales ID</th>
                <th>Customer Name</th>
                <th>Date</th>
                <th>Quantity</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @if (count($holdTransactions) == 0)
                <tr>
                    <td colspan="6" class="text-center">No Hold Transactions Found</td>
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
                        {{-- <td>HOLD-{{ $sid+1 }}</td> --}}
                        <td>{{ $transaction->invoice_number }}</td>

                        @if (auth()->user()->hasRole('warehouse'))
                            <td>{{ !empty($transaction->partyUser) ? $transaction->partyUser->first_name : 'N/A' }}</td>
                        @else
                            <td>{{ !empty($transaction->commissionUser) ? $transaction->commissionUser->first_name : 'N/A' }}
                            </td>
                        @endif

                        <td>{{ $transaction->hold_date }}</td>
                        <td>{{ $sumqty }}</td>
                        <td>₹{{ $transaction->total }}</td>
                        <td>
                            <div class="d-flex gap-1 action-buttons-block justify-content-center">
                                <button
                                    wire:click="resumeTransaction('{{ $transaction->id }}', '{{ $transaction->commission_user_id }}', '{{ $transaction->party_user_id }}')"
                                    class="btn btn-success btn-sm px-3">
                                    Resume
                                </button>
                                <button class="pdf-view"
                                    wire:click="printInvoice('{{ $transaction->id }}')">
                                    <img src="{{ asset('assets/images/sidebar-imgs/pdf-ic.svg') }}" alt="PDF">
                                </button>
                                <button class="btn btn-danger btn-sm ms-2 remove-item"
                                    onclick="confirmDelete({{ $transaction->id }})">
                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
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
