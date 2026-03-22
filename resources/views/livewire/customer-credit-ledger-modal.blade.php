<div>
    <style>
        .modal-content {
            border-radius: 12px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .modal-body {
            background: #ffffff !important;
        }
    </style>
    <!-- Trigger Button -->
    <div class="" wire:click="openModal" title="Customer Credit Ledger" style="cursor: pointer;">
        <button type="button" class="btn btn-default p-1 m-0 border-0 bg-transparent">
            <img src="{{ asset('public/external/customer_credit_icon_final.jpg') }}" alt="Customer Credit Ledger Icon" />
        </button>
        <span class="ic-txt">Credit Ledger</span>
    </div>

    <!-- Main Modal -->
    @if ($showModal)
        <div class="modal d-block" tabindex="-1"
            style="background-color: rgba(0,0,0,0.5);"wire:keydown.escape="$set('showModal', false)">
            <div class="modal-dialog modal-xl modal-dialog-scrollable customer-ledger-modal">
                <div class="modal-content">
                    <div class="modal-header custom-modal-header">
                        <h5 class="modal-title">Customer Credit Ledger</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Date Range Filter -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="date" wire:model.lazy="startDate" class="form-control"
                                    placeholder="From Date">
                            </div>
                            <div class="col-md-3">
                                <input type="date" wire:model.lazy="endDate" class="form-control"
                                    placeholder="To Date">
                            </div>
                        </div>

                        <!-- Search Box -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </span>
                                <input type="text" wire:model.live="search" class="form-control"
                                    placeholder="Search by name, phone, or credit points...">
                            </div>
                        </div>

                        <table style="width:100%; margin-bottom: 10px;" class="credit-debit-table">
                            <tr>
                                <td style="text-align: left;">
                                    <strong>Credit:</strong> {{ number_format($totalCredit, 2) }} Cr
                                </td>
                                <td style="text-align: right;">
                                    <strong>Net Outstanding:</strong> {{ number_format($netOutstanding, 2) }} Dr
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: left;">
                                    <strong>Debit:</strong> {{ number_format($totalDebit, 2) }} Dr
                                </td>
                                <td style="text-align: right;"class="pdf_ic">
                                    <button wire:click="downloadPDF" class="btn btn-danger">
                                        <i class="fas fa-file-pdf"></i> Download PDF
                                    </button>
                                </td>
                            </tr>
                        </table>

                        <div class="table-responsive credit-ledger-table">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light table-info">
                                    <tr>
                                        <th class="text-nowrap">Sr. No.</th>
                                        <th class="text-nowrap">Invoice No</th>
                                        <th class="text-nowrap">Party Customer</th>
                                        <th class="text-nowrap">Type</th>
                                        <th class="text-nowrap">Total Amount</th>
                                        <th class="text-nowrap">Credit</th>
                                        <th class="text-nowrap">Debit</th>
                                        <th class="text-nowrap">Status</th>
                                        <th class="text-nowrap">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($creditLedgers as $index => $ledger)
                                        <tr>
                                            <td>{{ $creditLedgers->firstItem() + $index }}</td>
                                            <td>
                                                <a href="javascript:void(0)"
                                                    wire:click="openInvoiceModal({{ $ledger->invoice_id }})"
                                                    class="badge badge-info text-info">
                                                    {{ $ledger->invoice_number }}
                                                </a>
                                            </td>
                                            <td>{{ $ledger->party_user ?? 'N/A' }}</td>
                                            <td>{{ ucfirst($ledger->type) }}</td>
                                            <td>{{ number_format($ledger->total_amount, 2) }}</td>
                                            <td>{{ number_format($ledger->credit_amount, 2) }}</td>
                                            <td>{{ number_format($ledger->debit_amount, 2) }}</td>

                                            <td>{{ $ledger->status }}</td>
                                            <td>{{ \Carbon\Carbon::parse($ledger->created_at)->format('Y-m-d H:i:s') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">
                                                @if ($search)
                                                    No records found for "{{ $search }}"
                                                @else
                                                    No credit history records available
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $creditLedgers->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showInvoiceModal)
        <div class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.3); z-index:1055;">

            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content shadow-lg border-0 rounded">

                    <!-- HEADER -->
                    <div class="modal-header text-white">
                        <h5 class="modal-title">
                            Invoice #{{ $selectedInvoice->invoice_number }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showInvoiceModal', false)">
                        </button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body p-4 bg-white">

                        @if ($selectedInvoice)

                            <!-- CUSTOMER + DATE -->
                            <div class="mb-4 text-center">
                                <h5 class="mb-1">
                                    Hello, {{ $selectedInvoice->customer_name ?? 'Customer' }}
                                </h5>
                                <small class="text-muted">
                                    Date:
                                    {{ \Carbon\Carbon::parse($selectedInvoice->created_at)->format('d M Y H:i') }}
                                </small>
                            </div>

                            <!-- ITEMS TABLE -->
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle text-center bg-white">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th class="text-start">Item</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($selectedInvoice->items as $i => $item)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td class="text-start">{{ $item['name'] }}</td>
                                                <td>{{ $item['quantity'] }}</td>
                                                <td>₹{{ number_format($item['mrp'], 2) }}</td>
                                                <td>
                                                    <strong>
                                                        ₹{{ number_format($item['mrp'] * $item['quantity'], 2) }}
                                                    </strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- SUMMARY -->
                            <div class="row mt-4">
                                <div class="col-lg-4 ms-auto">

                                    @php
                                        $subTotal = (float) $selectedInvoice->sub_total;
                                        $commission = (float) $selectedInvoice->commission_amount;
                                        $party = (float) $selectedInvoice->party_amount;
                                        $roundoff = (float) $selectedInvoice->roundof;

                                        $deduction = $commission > 0 ? $commission : $party;
                                        $grandTotal = $subTotal - $deduction + $roundoff;
                                    @endphp

                                    <div class="border rounded p-3 shadow-sm bg-white">

                                        <h6 class="mb-3 fw-bold text-dark">Transaction Details</h6>

                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-dark">Payment Mode</span>
                                            <span
                                                class="text-dark">{{ ucfirst($selectedInvoice->payment_mode) }}</span>
                                        </div>

                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-dark">Credit</span>
                                            <span class="text-dark">
                                                {{ $selectedInvoice->creditpay > 0 ? '₹' . number_format($selectedInvoice->creditpay, 2) : '-' }}
                                            </span>
                                        </div>

                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-dark">Sub Total</span>
                                            <span class="text-dark">₹{{ number_format($subTotal, 2) }}</span>
                                        </div>

                                        @if ($commission > 0)
                                            <div class="d-flex justify-content-between mb-2 text-danger">
                                                <span>Commission</span>
                                                <span>- ₹{{ number_format($commission, 2) }}</span>
                                            </div>
                                        @endif

                                        @if ($party > 0)
                                            <div class="d-flex justify-content-between mb-2 text-danger">
                                                <span>Party Deduction</span>
                                                <span>- ₹{{ number_format($party, 2) }}</span>
                                            </div>
                                        @endif

                                        @if ($roundoff > 0)
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-dark">Round Off</span>
                                                <span class="text-dark">₹{{ number_format($roundoff, 2) }}</span>
                                            </div>
                                        @endif

                                        <hr>

                                        <div class="d-flex justify-content-between fw-bold text-dark">
                                            <span>Total</span>
                                            <span style="color:#0d6efd; font-size:20px;">
                                                ₹{{ number_format($grandTotal, 2) }}
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- FOOTER -->
                            <div class="mt-4 text-center">
                                <small class="text-muted">
                                    Thank you for your business.
                                </small>
                            </div>

                        @endif

                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
