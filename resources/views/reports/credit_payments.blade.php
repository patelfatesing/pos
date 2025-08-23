@extends('layouts.backend.datatable_layouts')

@section('styles')
<style>
  .custom-toolbar-row{display:flex;flex-wrap:wrap;align-items:center;gap:1rem;margin-bottom:1rem;}
  .custom-toolbar-row .dataTables_length{order:1;}
  .custom-toolbar-row .dt-buttons{order:2;}
  .custom-toolbar-row .filters{order:3;display:flex;gap:.5rem;flex-wrap:wrap;}
  .custom-toolbar-row .dataTables_filter{order:4;margin-left:auto;}
  .dt-buttons .btn{margin-right:5px;}
  @media(max-width:768px){.custom-toolbar-row>div{flex:1 1 100%;margin-bottom:10px;}}
  .w-140{width:140px}
</style>
@endsection

@section('page-content')
<div class="wrapper">
  <div class="content-page">
    <div class="container-fluid">

      <div class="row align-items-center mb-3">
        <div class="col-lg-12">
          <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div><h4 class="mb-3">Credit Payment Report</h4></div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="row g-2 mb-2">
        <div class="col-md-2">
          <select id="branch_id" class="form-control">
            <option value="">All Branches</option>
            @foreach($branches as $b)
              <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select id="party_user_id" class="form-control">
            <option value="">All Party Customers</option>
            @foreach($parties as $p)
              <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <select id="type" class="form-control">
            <option value="">Type: All</option>
            <option value="debit">Debit (Payment)</option>
            <option value="credit">Credit (On Credit)</option>
          </select>
        </div>
        <div class="col-md-2">
          <select id="transaction_kind" class="form-control">
            <option value="">Kind: All</option>
            <option value="order">Order</option>
            <option value="refund">Refund</option>
            <option value="collact_credit">Credit Collection</option>
          </select>
        </div>
        <div class="col-md-1">
          <select id="status" class="form-control">
            <option value="">Status</option>
            <option value="paid">Paid</option>
            <option value="partial_paid">Partial</option>
            <option value="unpaid">Unpaid</option>
          </select>
        </div>
        <div class="col-md-1">
          <input type="date" id="start_date" class="form-control w-140" />
        </div>
        <div class="col-md-1">
          <input type="date" id="end_date" class="form-control w-140" />
        </div>
      </div>

      <div class="table-responsive rounded">
        <table class="table table-striped table-bordered nowrap" id="credit_payments_table" style="width:100%;">
          <thead class="bg-white">
            <tr class="ligth ligth-data">
              <th>Sr No</th>
              <th>Date</th>
              <th>Branch</th>
              <th>Party</th>
              <th>Invoice</th>
              <th>Type</th>
              <th>Kind</th>
              <th>Total</th>
              <th>Credit</th>
              <th>Debit</th>
              <th>Net (Cr−Dr)</th>
              <th>Status</th>
              <th>By</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
$(function(){
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  const table = $('#credit_payments_table').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: {
      url: "{{ route('reports.credit_payments.get_data') }}",
      type: 'POST',
      data: function(d){
        d.branch_id        = $('#branch_id').val();
        d.party_user_id    = $('#party_user_id').val();
        d.type             = $('#type').val();
        d.transaction_kind = $('#transaction_kind').val();
        d.status           = $('#status').val();
        d.start_date       = $('#start_date').val();
        d.end_date         = $('#end_date').val();
      }
    },
    columns: [
      { data: 'sr_no', orderable:false, searchable:false },
      { data: 'tx_date' },
      { data: 'branch_name' },
      { data: 'party_name' },
      { data: 'invoice' },
      { data: 'type' },           // badge HTML
      { data: 'kind' },
      { data: 'total_amount' },
      { data: 'credit_amount' },
      { data: 'debit_amount' },
      { data: 'net_amount' },
      { data: 'status' },         // badge HTML
      { data: 'created_by' },
      { data: 'action', orderable:false, searchable:false },
    ],
    order: [[1, 'desc']], // date desc
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,"All"]],
    pageLength: 10,
    dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
    buttons: [
      { extend:'excelHtml5', className:'btn btn-outline-success btn-sm me-2',
        title:'Credit Payment Report', filename:'credit_payment_report',
        exportOptions:{ columns:':visible' } },
      { extend:'pdfHtml5', className:'btn btn-outline-danger btn-sm',
        title:'Credit Payment Report', filename:'credit_payment_report',
        orientation:'landscape', pageSize:'A4',
        exportOptions:{ columns:':visible' } }
    ],
    initComplete: function(){
      $('#branch_id, #party_user_id, #type, #transaction_kind, #status, #start_date, #end_date').on('change', function(){
        table.ajax.reload();
      });
    }
  });
});
</script>
@endsection
