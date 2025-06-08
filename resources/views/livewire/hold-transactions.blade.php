<div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Sales ID</th>
                <th>Customer Name</th>
                <th>Date</th>
                <th>Quantity</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
           
            @foreach($holdTransactions as $sid => $transaction)
            @php
                $sumqty=0;

                foreach ($transaction->items as $key =>$item) {
                   $sumqty+=$item['quantity'];
                }
            @endphp
                <tr>
                    {{-- <td>HOLD-{{ $sid+1 }}</td> --}}
                    <td>{{ $transaction->invoice_number }}</td>

                    @if (auth()->user()->hasRole('warehouse'))
                    <td>{{ !empty($transaction->partyUser) ? $transaction->partyUser->first_name : 'N/A' }}</td>
                    @else
                    <td>{{ !empty($transaction->commissionUser) ? $transaction->commissionUser->first_name: 'N/A' }}</td>

                    @endif

                    <td>{{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                    <td>{{ $sumqty }}</td>
                    <td>₹{{ $transaction->total}}</td>
                    <td>
                        <button wire:click="resumeTransaction('{{ $transaction->id }}', '{{ $transaction->commission_user_id }}', '{{ $transaction->party_user_id }}')" class="btn btn-success btn-sm">
                            Resume
                        </button>
                        <button class="btn btn-primary btn-sm" wire:click="printInvoice('{{ $transaction->id }}')">
                          <i class="fas fa-print"></i>
                          </button>
                        <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $transaction->id }})">
                            Delete
                        </button>
                    </td>
                </tr>
            @endforeach
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