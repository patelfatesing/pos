@extends('layouts.backend.datatable_layouts')

@section('page-content')

<div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

        <!-- FILTERS -->
        <div class="row mb-3">
            <div class="col-md-3">
                <input type="date" id="from_date" class="form-control" value="{{ date('Y-m-01') }}">
            </div>

            <div class="col-md-3">
                <input type="date" id="to_date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>

            <div class="col-md-3">
                <select id="branch_id" class="form-control">
                    <option value="">All Branch</option>
                    @foreach($branches as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <button class="btn btn-primary w-100" id="filter">Filter</button>
            </div>
        </div>

        <!-- TABLE -->
        <table class="table table-bordered" id="stockTable">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Opening</th>
                    <th>Inward</th>
                    <th>Outward</th>
                    <th>Adjustment</th>
                    <th>Closing</th>
                </tr>
            </thead>
        </table>
            </div>
        </div>
    </div>


@endsection

@section('scripts')

<script>
let table = $('#stockTable').DataTable({
    processing: true,
    serverSide: false,
    paging: false,
    searching: false,
    info: false,
    ordering: false,

    ajax: {
        url: "{{ route('accounting.stock.summary.data') }}",
        data: function(d){
            d.from_date = $('#from_date').val();
            d.to_date   = $('#to_date').val();
        },

        dataSrc: function(json){

            let data = [];
            let grouped = {};

            // GROUP BY CATEGORY
            json.data.forEach(row => {
                if(!grouped[row.category]){
                    grouped[row.category] = [];
                }
                grouped[row.category].push(row);
            });

            let total = {
                opening: 0,
                inward: 0,
                outward: 0,
                adjustment: 0,
                closing: 0
            };

            // LOOP CATEGORY
            Object.keys(grouped).forEach(category => {

                let items = grouped[category];

                let cat = {
                    opening: 0,
                    inward: 0,
                    outward: 0,
                    adjustment: 0,
                    closing: 0
                };

                items.forEach(r => {
                    cat.opening += parseFloat(r.opening);
                    cat.inward += parseFloat(r.inward);
                    cat.outward += parseFloat(r.outward);
                    cat.adjustment += parseFloat(r.adjustment);
                    cat.closing += parseFloat(r.closing);
                });

                // CATEGORY ROW (YELLOW)
                data.push({
                    category: category,
                    product: '',
                    opening: cat.opening,
                    inward: cat.inward,
                    outward: cat.outward,
                    adjustment: cat.adjustment,
                    closing: cat.closing,
                    is_category: true
                });

                // PRODUCT ROWS
                items.forEach(r => {
                    data.push({
                        category: '',
                        product: '   ' + r.product,
                        opening: r.opening,
                        inward: r.inward,
                        outward: r.outward,
                        adjustment: r.adjustment,
                        closing: r.closing,
                        is_category: false
                    });
                });

                // ADD TOTAL
                total.opening += cat.opening;
                total.inward += cat.inward;
                total.outward += cat.outward;
                total.adjustment += cat.adjustment;
                total.closing += cat.closing;
            });

            // GRAND TOTAL
            data.push({
                category: 'Grand Total',
                product: '',
                opening: total.opening,
                inward: total.inward,
                outward: total.outward,
                adjustment: total.adjustment,
                closing: total.closing,
                is_total: true
            });

            return data;
        }
    },

    columns: [
        {data: 'category'},
        {data: 'product'},
        {data: 'opening', className:'text-right'},
        {data: 'inward', className:'text-right'},
        {data: 'outward', className:'text-right'},
        {data: 'adjustment', className:'text-right'},
        {data: 'closing', className:'text-right'},
    ],

    rowCallback: function(row, data) {
        if(data.is_category){
            $(row).css({
                'background':'#f4b400',
                'font-weight':'bold'
            });
        }

        if(data.is_total){
            $(row).css({
                'background':'#eaeaea',
                'font-weight':'bold'
            });
        }
    }
});

// FILTER
$('#filter').click(function(){
    table.ajax.reload();
});
</script>

@endsection