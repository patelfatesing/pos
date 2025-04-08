@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <h1>Invoice #{{ $invoice->invoice_number }}</h1>
                <div class="d-flex justify-content-between align-items-center mb-3 no-print">
                    <a href="{{ route('invoice.download', $invoice->id) }}" class="btn btn-success ">📄 Download PDF</a>
                    <button onclick="window.print()" class="btn btn-secondary">🖨️ Print</button>
                </div>

                <hr>

                <p><strong>Date:</strong> {{ $invoice->created_at->format('d M Y') }}</p>

                <div class="row">
                    @if(!empty($commissionUser))
                    <div class="col-md-6">
                        <p><strong>Commission User:</strong> {{ $commissionUser->first_name }} {{ $commissionUser->last_name }}</p>
                    </div>
                    @elseif(!empty($partyUser))
                    <div class="col-md-6">
                        <p><strong>Party User:</strong> {{ $partyUser->first_name }} {{ $partyUser->last_name }}</p>
                    </div>
                    @endif
                </div>

                <table class="table table-bordered datatable" id="invoice_items_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>₹{{ number_format($item['price'], 2) }}</td>
                            <td>₹{{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="text-end">
                    <p><strong>Subtotal:</strong> ₹{{ number_format($invoice->sub_total, 2) }}</p>
                    <p><strong>Tax (18%):</strong> ₹{{ number_format($invoice->tax, 2) }}</p>
                    @if($invoice->commission_amount > 0)
                        <p><strong>Commission Deduction:</strong> - ₹{{ number_format($invoice->commission_amount, 2) }}</p>
                    @endif
                    @if($invoice->party_amount > 0)
                        <p><strong>Party Deduction:</strong> - ₹{{ number_format($invoice->party_amount, 2) }}</p>
                    @endif
                    <h5><strong>Total:</strong> ₹{{ number_format($invoice->total, 2) }}</h5>
                </div>
            </div>
        </div>
    </div>
    <!-- Wrapper End -->

    <script>
        $(document).ready(function() {
            $('#invoice_items_table').DataTable({
                pageLength: 10,
                responsive: true,
                dom: 'Bfrtip',
                buttons: ['pageLength'],
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows']
                ]
            });
        });
    </script>
@endsection
