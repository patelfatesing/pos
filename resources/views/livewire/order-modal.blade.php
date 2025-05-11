<div>
    <!-- Trigger Button -->
    <button wire:click="openModal" class="btn btn-primary ml-2" title="Sales History">
        <i class="fa fa-list"></i>
    </button>

    <!-- Modal -->
    @if($showModal)
    <div class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:keydown.escape="$set('showModal', false)">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sales History</h5>
                   
                    <button type="button" class="close" wire:click="$set('showModal', false)">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if(count($orders) > 0)
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Sr</th>
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Customer Name</th>
                                    <th>Payment Mode</th>
                                    <th>Total Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                           <tbody>
                                @foreach($orders as $index => $order)
                                    <tr class="{{ $order->status === 'Refunded' ? 'table-warning' : '' }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $order->invoice_number }}</td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i' ) }}</td>
                                        <td>
                                            @if(auth()->user()->hasRole('warehouse'))
                                                {{ optional($order->partyUser)->first_name }} {{ optional($order->partyUser)->last_name }}
                                            @else
                                                {{ optional($order->commissionUser)->first_name }} {{ optional($order->commissionUser)->last_name }}
                                            @endif
                                        </td>
                                        <td>Cash</td>
                                        <td>{{ format_inr($order->total) }}</td>
                                        <td class="text-center">
                                            <a href="{{ asset('storage/invoices/' . $order->invoice_number . '.pdf') }}" class="btn btn-sm btn-secondary" target="_blank">
                                                <i class="fa fa-file-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    @else
                        <p class="text-muted">No Data found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
