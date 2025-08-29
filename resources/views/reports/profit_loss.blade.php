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
<?php
dd('ddfg');

?>
@section('page-content')
<div class="wrapper">
  <div class="content-page">
    <div class="container-fluid">

      <div class="row align-items-center mb-3">
        <div class="col-lg-12">
          <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div><h4 class="mb-3">Profit &amp; Loss Report</h4></div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="row g-2 mb-2">
        <div class="col-md-3">
          <select id="branch_id" class="form-control">
            <option value="">All Branches</option>
            @foreach($branches as $b)
              <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <input type="date" id="start_date" class="form-control w-140" />
        </div>
        <div class="col-md-3">
          <input type="date" id="end_date" class="form-control w-140" />
        </div>
        <div class="col-md-3">
          <button id="apply_filters" class="btn btn-primary">Apply</button>
          <button id="reset_filters" class="btn btn-outline-secondary">Reset</button>
        </div>
      </div>

      <div class="table-responsive rounded">
        <table class="table table-striped table-bordered nowrap" id="pl_table" style="width:100%;">
          <thead class="bg-white">
            <tr class="ligth ligth-data">
              <th>Sr No</th>
              <th>Date</th>
              <th>Branch</th>
              <th>Bills</th>
              <th>Net Sales</th>
              <th>Discounts</th>     <!-- 👈 added -->
              <th>Tax</th>
              <th>Total Sales</th>
              <th>Refunds</th>
              <th>COGS</th>
              <th>Gross Profit</th>
              <th>Expenses</th>
              <th>Net Profit</th>
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

  const table = $('#pl_table').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: {
      url: "{{ route('reports.pl.get_data') }}",
      type: 'POST',
      data: function(d){
        d.branch_id  = $('#branch_id').val();
        d.start_date = $('#start_date').val();
        d.end_date   = $('#end_date').val();
      }
    },
    columns: [
      { data: 'sr_no', orderable:false, searchable:false },
      { data: 'date' },
      { data: 'branch_name' },
      { data: 'bills' },
      { data: 'net_sales' },
      { data: 'discounts' },     // 👈 added
      { data: 'tax' },
      { data: 'total_sales' },
      { data: 'refunds' },
      { data: 'cogs' },
      { data: 'gross_profit' },
      { data: 'expenses' },
      { data: 'net_profit' },
      { data: 'action', orderable:false, searchable:false },
    ],
    order: [[1, 'desc']], // date desc
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,"All"]],
    pageLength: 10,
    dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
    buttons: [
      { extend:'excelHtml5', className:'btn btn-outline-success btn-sm me-2',
        title:'Profit & Loss', filename:'profit_loss_excel',
        exportOptions:{ columns:':visible' } },
      { extend:'pdfHtml5', className:'btn btn-outline-danger btn-sm',
        title:'Profit & Loss', filename:'profit_loss_pdf',
        orientation:'landscape', pageSize:'A4',
        exportOptions:{ columns:':visible' } }
    ],
    initComplete: function(){
      $('#apply_filters').on('click', function(){ table.ajax.reload(); });
      $('#reset_filters').on('click', function(){
        $('#branch_id').val('');
        $('#start_date').val('');
        $('#end_date').val('');
        table.ajax.reload();
      });
    }
  });
});
</script>
@endsection
