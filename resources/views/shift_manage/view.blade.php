    @extends('layouts.backend.layouts')
    <style>
        .form-group {
            margin-bottom: 0rem !important;
        }
    </style>

    @section('page-content')
        <!-- Wrapper Start -->
        <div class="wrapper">
            <div class="content-page">
                <div class="container-fluid add-form-list">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <!-- Header Title Section -->
                                    <div class="header-title">
                                        <h4 class="card-title">View Transaction - {{ $branch_name }}</h4>
                                    </div>

                                    <!-- Buttons Section -->
                                    <div class="d-flex">
                                        <a href="{{ route('sales.add-sales', ['branch_id' => $id, 'shift_id' => $shift_id]) }}"
                                            class="btn btn-primary-dark mr-2">
                                            <i class="fa fa-edit"></i> Add Trasaction
                                        </a>
                                        <a href="{{ route('shift-manage.list') }}" class="btn btn-secondary">
                                            Back
                                        </a>
                                    </div>
                                </div>

                                <div class="card-body">

                                    <!-- Show Entries Form -->
                                    <!-- Show Entries + Search Form Row -->
                                    <form method="GET" id="perPageForm" class="mb-3">
                                        <input type="hidden" name="id" value="{{ $id }}">
                                        <input type="hidden" name="shift_id" value="{{ $shift_id }}">

                                        <div class="row align-items-center justify-content-between g-3">
                                            <!-- Left: Show Entries -->
                                            <div class="col-md-auto d-flex align-items-center">
                                                <label for="per_page" class="me-2 mb-0">Show</label>
                                                <select name="per_page" id="per_page" class="form-select w-auto me-2">
                                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10
                                                    </option>
                                                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25
                                                    </option>
                                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50
                                                    </option>
                                                    <option value="{{ $invoices->total() }}"
                                                        {{ $perPage == $invoices->total() ? 'selected' : '' }}>All</option>
                                                </select>
                                                <span>entries</span>
                                            </div>

                                            <!-- Right: Search Box -->
                                            <div class="col-md-4 ms-auto">
                                                <div class="input-group">
                                                    <input type="text" name="search" value="{{ request('search') }}"
                                                        class="form-control" placeholder="Search product name...">
                                                    <button class="btn btn-primary" type="submit">Search</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Table -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Invoice No</th>
                                                    <th>Cash Amount</th>
                                                    <th>UPI Amount</th>
                                                    <th>Credit Pay</th>
                                                    <th>Payment Mode</th>
                                                    <th>Total Items</th>
                                                    <th>Sub Total</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($invoices as $invoice)
                                                    <tr>
                                                        <td>
                                                            @if ($invoice->status == 'Hold')
                                                                <a class="badge badge-success"
                                                                    href="{{ url('/view-hold-invoice/' . $invoice->id . '/' . $shift_id) }}">{{ $invoice->invoice_number }}</a>
                                                            @else
                                                                <a class="badge badge-success"
                                                                    href="{{ url('/view-invoice/' . $invoice->id . '/' . $shift_id) }}">{{ $invoice->invoice_number }}</a>
                                                            @endif
                                                        </td>
                                                        <td>{{ number_format($invoice->cash_amount, 2) }}</td>
                                                        <td>{{ number_format($invoice->upi_amount + $invoice->online_amount, 2) }}
                                                        </td>
                                                        <td>{{ number_format($invoice->creditpay, 2) }}</td>
                                                        <td>{{ $invoice->payment_mode }}</td>
                                                        <td>{{ $invoice->total_item_qty }}</td>
                                                        <td>{{ number_format($invoice->sub_total, 2) }}</td>
                                                        <td>{{ number_format($invoice->total, 2) }}</td>
                                                        <td>{{ $invoice->status }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($invoice->created_at)->format('d-m-Y h:i:s') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <th class="text-end">Total</th>
                                                    <th>{{ number_format($totalCashAmount, 2) }}</th>
                                                    <th>{{ number_format($totalUPIAmount, 2) }}</th>
                                                    <th>{{ number_format($totalCreditPay, 2) }}</th>
                                                    <th></th>
                                                    <th>{{ $totalItems }}</th>
                                                    <th>{{ number_format($totalSubTotal, 2) }}</th>
                                                    <th>{{ number_format($totalTotal, 2) }}</th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>

                                        <!-- Pagination -->
                                        <div class="d-flex justify-content-center mt-3">
                                            {{ $invoices->appends(['per_page' => $perPage])->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Page end -->
                </div>
            </div>
        </div>

        <!-- Auto-submit JS -->
        <script>
            document.getElementById('per_page').addEventListener('change', function() {
                document.getElementById('perPageForm').submit();
            });
        </script>
    @endsection
