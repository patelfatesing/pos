<div>
    <!-- Trigger Button -->
    <button wire:click="openModal" class="btn btn-default ml-2" title="Customer Credit Ledger">
        <img src="{{ asset('public/external/customer_credit_icon_final.jpg') }}" alt="Customer Credit Ledger Icon" />
    </button>

    <!-- Main Modal -->
    @if ($showModal)
        <div class="modal d-block" tabindex="-1"
            style="background-color: rgba(0,0,0,0.5);"wire:keydown.escape="$set('showModal', false)">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Customer Credit Ledger</h5>
                        <button type="button" class="close" wire:click="$set('showModal', false)">
                            <span aria-hidden="true">×</span>
                        </button>
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
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" wire:model.live="search" class="form-control"
                                    placeholder="Search by name, phone, or credit points...">
                            </div>
                        </div>

                        <table style="width:100%; margin-bottom: 10px;">
                            <tr>
                                <td><strong>Credit</strong> {{ number_format($totalCredit, 2) }} Cr</td>
                            </tr>
                            <tr>
                                <td><strong>Debit</strong> {{ number_format($totalDebit, 2) }} Dr</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><strong>Net Outstanding</strong> {{ number_format($netOutstanding, 2) }} Dr</td>
                            </tr>
                        </table>


                        <!-- Download PDF Button -->
                        <div class="mb-3 text-end">
                            <button wire:click="downloadPDF" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped nowrap">
                                <thead class="table-light">
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
                                                <a href="{{ url('/view-invoice/' . $ledger->invoice_id) }}"
                                                    class="badge badge-info">
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
</div>
