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
                    <ul class="nav nav-tabs" id="salesTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="paid-tab" data-bs-toggle="tab" href="#paid" role="tab" aria-controls="paid" aria-selected="true">Sales</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="refunded-tab" data-bs-toggle="tab" href="#refunded" role="tab" aria-controls="refunded" aria-selected="false">Refunds</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="salesTabContent">
                        <!-- Paid Orders Tab -->
                        <div class="tab-pane fade show active" id="paid" role="tabpanel" aria-labelledby="paid-tab">
                            @if($orders->where('status', 'Paid')->count() > 0)
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sr</th>
                                            <th>Invoice No</th>
                                            <th>Date</th>
                                            <th>Customer Name</th>
                                            <th>Payment Mode</th>
                                            <th>Qty</th>
                                            <th>Total Amount</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $j=1;
                                        @endphp
                                        @foreach($orders->where('status', 'Paid') as $index => $order)
                                            <tr>
                                                <td>{{ $j++ }}</td>
                                                <td>{{ $order->invoice_number }}</td>
                                                <td>{{ $order->created_at->format('d/m/Y H:i' ) }}</td>
                                                <td>
                                                    @if(auth()->user()->hasRole('warehouse'))
                                                        {{ optional($order->partyUser)->first_name }} 
                                                    @else
                                                        {{ optional($order->commissionUser)->first_name }} 
                                                    @endif
                                                </td>
                                                <td>{{($order->payment_mode=="online")?"UPI":$order->payment_mode}}</td>
                                                <td>{{ $order->total_item_qty }}</td>
                                                 <td>{{ format_inr($order->total) }}</td>
                                                    <td class="text-center">
                                            <button class="btn btn-sm btn-secondary" wire:click="printInvoice('{{ $order->id }}')">
                                            <i class="fa fa-file-pdf"></i>
                                            </button>


                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-muted">No Paid Orders found.</p>
                            @endif
                        </div>

                        <!-- Refunded Orders Tab -->
                        <div class="tab-pane fade" id="refunded" role="tabpanel" aria-labelledby="refunded-tab">
                        @if($refunds->count() > 0)
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sr</th>
                                        <th>Invoice No</th>
                                        <th>Date</th>
                                        <th>Customer Name</th>
                                        <th>Payment Mode</th>
                                        <th>Qty</th>
                                        <th>Total Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($refunds as $index => $refund)
                                        @php $order = $refund->invoice; @endphp
                                        <tr class="table-warning">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $order->invoice_number ?? '-' }}</td>
                                            <td>{{ $refund->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td>
                                                @if(auth()->user()->hasRole('warehouse'))
                                                    {{ optional($order->partyUser)->first_name }}
                                                    {{ optional($order->commissionUser)->first_name }} 
                                                @endif
                                            </td>
                                            <td>{{ $order->payment_mode ?? '-' }}</td>
                                            <td>{{ $refund->total_item_qty ?? 0 }}</td>
                                            <td>{{ format_inr($refund->amount ?? 0) }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-secondary" wire:click="printRefundInvoice('{{ asset('storage/invoices/refund_' . $order->invoice_number . '.pdf') }}')">
                                                    <i class="fa fa-file-pdf"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted">No Refunded Orders found.</p>
                        @endif
                    </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
</div>