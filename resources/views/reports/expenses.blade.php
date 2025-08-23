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
  .w-120{width:120px}
  .summary-badges{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.5rem}
</style>
@endsection

@section('page-content')
<div class="wrapper">
  <div class="content-page">
    <div class="container-fluid">

      <div class="row align-items-center mb-3">
        <div class="col-lg-12">
          <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <h4 class="mb-3">Expense Report</h4>
          </div>
          <div class="summary-badges">
            <span class="badge bg-dark">
              Total Amount: <span id="total_amount_badge">0.00</span>
            </span>
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
        <div class="col-md-2">
          <select id="expense_category_id" class="form-control">
            <option value="">All Categories</option>
            @foreach($categories as $c)
              <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <select id="user_id" class="form-control">
            <option value="">All Users</option>
            @foreach($users as $u)
              <option value="{{ $u->id }}">{{ $u->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <input type="date" id="start_date" class="form-control w-120" />
        </div>
        <div class="col-md-2">
          <input type="date" id="end_date" class="form-control w-120" />
        </div>
        <div class="col-md-1">
          <input type="number" step="0.01" id="min_amount" class="form-control" placeholder="Min ₹" />
        </div>
        <div class="col-md-1">
          <input type="number" step="0.01" id="max_amount" class="form-control" placeholder="Max ₹" />
        </div>
      </div>

      <div class="table-responsive rounded">
        <table class="table table-striped table-bordered nowrap" id="expenses_table" style="width:100%;">
          <thead class="bg-white">
            <tr class="ligth ligth-data">
              <th>Sr No</th>
              <th>Date</th>
              <th>Branch</th>
              <th>Category</th>
              <th>Title</th>
              <th>Description</th>
              <th>Amount</th>
              <th>Created By</th>
              <th>Created At</th>
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

  const table = $('#expenses_table').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: {
      url: "{{ route('reports.expenses.get_data') }}",
      type: 'POST',
      data: function(d){
        d.branch_id           = $('#branch_id').val();
        d.expense_category_id = $('#expense_category_id').val();
        d.user_id             = $('#user_id').val();
        d.start_date          = $('#start_date').val();
        d.end_date            = $('#end_date').val();
        d.min_amount          = $('#min_amount').val();
        d.max_amount          = $('#max_amount').val();
      }
    },
    columns: [
      { data: 'sr_no', orderable:false, searchable:false },
      { data: 'expense_date' },
      { data: 'branch_name' },
      { data: 'category' },
      { data: 'title' },
      { data: 'description' },
      { data: 'amount' },
      { data: 'created_by' },
      { data: 'created_at' },
      { data: 'action', orderable:false, searchable:false },
    ],
    order: [[1,'desc']],
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,"All"]],
    pageLength: 10,
    dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
    buttons: [
      { extend:'excelHtml5', className:'btn btn-outline-success btn-sm me-2',
        title:'Expense Report', filename:'expense_report',
        exportOptions:{ columns:':visible' } },
      { extend:'pdfHtml5', className:'btn btn-outline-danger btn-sm',
        title:'Expense Report', filename:'expense_report',
        orientation:'landscape', pageSize:'A4',
        exportOptions:{ columns:':visible' } }
    ],
    initComplete: function(){
      $('#branch_id, #expense_category_id, #user_id, #start_date, #end_date, #min_amount, #max_amount')
        .on('change keyup', function(){ table.ajax.reload(); });
    }
  });

  // Update total badge after each draw
  $('#expenses_table').on('xhr.dt', function(e, settings, json, xhr){
    if (json && json.totals && typeof json.totals.amount !== 'undefined') {
      $('#total_amount_badge').text(parseFloat(json.totals.amount).toFixed(2));
    }
  });
});
</script>
@endsection
