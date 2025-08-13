<div>
    <!-- Trigger Button -->
    <div class="" wire:click="openModal" title="Sales History" style="cursor: pointer;">
        <button type="button" class="btn btn-default p-1 m-0 border-0 bg-transparent">
            <img src="{{ asset('external/orderhistory14471-2h8.svg') }}" alt="Sales History Icon" />
        </button>
        <span class="ic-txt">Sales History</span>
    </div>
    <!-- Modal -->
    @if ($showModal)
        <div class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);"
            wire:keydown.escape="$set('showModal', false)">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header custom-modal-header">
                        <h6 class="modal-title cash-summary-text61">Sales History</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            wire:click="$set('showModal', false)"></button>
                        </button>
                    </div>
                    <div class="modal-body sales-history-block">
                        <ul class="nav nav-tabs" id="salesTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="paid-tab" data-bs-toggle="tab" href="#paid"
                                    role="tab" aria-controls="paid" aria-selected="true">Sales</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="refunded-tab" data-bs-toggle="tab" href="#refunded"
                                    role="tab" aria-controls="refunded" aria-selected="false">Refunds</a>
                            </li>
                        </ul>
                        <div class="tab-content sales-history-block" id="salesTabContent">
                            <!-- Paid Orders Tab -->
                            <div class="tab-pane fade show active" id="paid" role="tabpanel"
                                aria-labelledby="paid-tab">
                                @if ($orders->count() > 0)
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-info">
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
                                                $j = 1;
                                            @endphp
                                            @foreach ($orders as $index => $order)
                                                <tr>
                                                    <td>{{ $j++ }}</td>
                                                    <td>{{ $order->invoice_number }}</td>
                                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        @if (auth()->user()->hasRole('warehouse'))
                                                            {{ optional($order->partyUser)->first_name }}
                                                        @else
                                                            {{ optional($order->commissionUser)->first_name }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $order->payment_mode == 'online' ? 'UPI' : $order->payment_mode }}
                                                    </td>
                                                    <td>{{ $order->total_item_qty }}</td>
                                                    @if ($order->creditpay + @$order->party_amount + @$order->commission_amount == $order->sub_total)
                                                        <td>{{ format_inr($order->creditpay) }}</td>
                                                    @else
                                                        <td>{{ format_inr($order->total) }}</td>
                                                    @endif
                                                    <td class="text-center">

                                                        <button class="btn btn-lg"
                                                            wire:click="printInvoice('{{ $order->id }}')">
                                                            <img src="{{ asset('assets/images/sidebar-imgs/pdf-ic.svg') }}"
                                                                alt="PDF">
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
                                @if ($refunds->count() > 0)
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Sr</th>
                                                <th>Refund No</th>
                                                <th>Date</th>
                                                <th>Customer Name</th>
                                                <th>Payment Mode</th>
                                                <th>Qty</th>
                                                <th>Total Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($refunds as $index => $refund)
                                                @php $order = $refund->invoice; @endphp
                                                <tr class="table-warning">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $refund->refund_number ?? '-' }}</td>
                                                    <td>{{ $refund->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                                    <td>
                                                        @if (auth()->user()->hasRole('warehouse'))
                                                            {{ optional($order->partyUser)->first_name }}
                                                            {{ optional($order->commissionUser)->first_name }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $order->payment_mode ?? '-' }}</td>
                                                    <td>{{ $refund->total_item_qty ?? 0 }}</td>
                                                    <td>{{ format_inr($refund->amount ?? 0) }}</td>
                                                    @if ($refund->type == 'return')
                                                        <td class="text-center">
                                                            <button class="btn btn-sm btn-secondary"
                                                                wire:click="printRefundInvoice('{{ asset('storage/invoices/return_' . $refund->refund_number . '.pdf') }}')">
                                                                <i class="fa fa-file-pdf"></i>
                                                            </button>
                                                        </td>
                                                    @else
                                                        <td class="text-center">
                                                            <button class="btn btn-sm btn-secondary"
                                                                wire:click="printRefundInvoice('{{ asset('storage/invoices/refund_' . $refund->refund_number . '.pdf') }}')">
                                                                <i class="fa fa-file-pdf"></i>
                                                            </button>
                                                        </td>
                                                    @endif
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
