@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Discount &amp; Offer Report</h4>
                    </div>
                    <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
                </div>

                <!-- Filters -->
                <div class="row g-2 mb-2 mt-2">
                    <div class="col-md-2">
                        <select id="branch_id" class="form-control">
                            <option value="">All Branches</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="party_user_id" class="form-control">
                            <option value="">All Party Customers</option>
                            @foreach ($parties as $p)
                                <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="payment_mode" class="form-control">
                            <option value="">Payment: All</option>
                            <option value="cash">Cash</option>
                            <option value="online">UPI</option>
                            <option value="cashupi">CASE+UPI</option>

                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="start_date" class="form-control w-120" />
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="end_date" class="form-control w-120" />
                    </div>
                    {{-- <div class="col-md-1">
                        <input type="number" min="0" max="100" step="0.01" id="min_discount_pct"
                            class="form-control" placeholder="% ≥" title="Minimum Discount %" />
                    </div> --}}
                </div>

                <div class="table-responsive rounded">
                    <table class="table table-striped table-bordered nowrap" id="discounts_table">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th>Branch</th>
                                <th>Party</th>
                                <th>Subtotal</th>
                                <th>Commission Disc</th>
                                <th>Party Disc</th>
                                <th>Total Discount</th>
                                <th>Discount %</th>
                                <th>Net Before Tax</th>
                                <th>Tax</th>
                                <th>Computed Total</th>
                                <th>Payment Mode</th>
                                <th>Status</th>
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

var pdfLogo = "";

$(function(){

    $.ajaxSetup({
        headers:{
            'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
        }
    });

    const table = $('#discounts_table').DataTable({

        processing:true,
        serverSide:true,
        responsive:true,

        language:{
            search:"",
            lengthMenu:"_MENU_"
        },

        ajax:{
            url:"{{ route('reports.discounts.get_data') }}",
            type:'POST',
            data:function(d){

                d.branch_id = $('#branch_id').val();
                d.party_user_id = $('#party_user_id').val();
                d.payment_mode = $('#payment_mode').val();
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();

            }
        },

        dom:"<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",

        columns:[

            {data:'sr_no', orderable:false, searchable:false},
            {data:'date'},
            {data:'invoice'},
            {data:'branch_name'},
            {data:'party_name'},
            {data:'sub_total'},
            {data:'commission_disc'},
            {data:'party_disc'},
            {data:'total_disc'},
            {data:'discount_pct'},
            {data:'net_before_tax'},
            {data:'tax'},
            {data:'computed_total'},
            {data:'payment_mode'},
            {data:'status'}

        ],

        order:[[1,'desc']],

        pageLength:10,

        buttons:[
        {
            extend:'collection',
            text:'<i class="fa fa-download"></i>',
            className:'btn btn-info btn-sm',
            autoClose:true,

            buttons:[
                {
                    extend:'excelHtml5',
                    text:'<i class="fa fa-file-excel-o"></i> Excel',
                    title:'Discount & Offer Report',
                    filename:'discount_offer_report',
                    exportOptions:{
                        columns:':visible'
                    }
                },

                {
                    extend:'pdfHtml5',
                    text:'<i class="fa fa-file-pdf-o"></i> PDF',
                    filename:'discount_offer_report',
                    orientation:'landscape',
                    pageSize:'A4',

                    exportOptions:{
                        columns:':visible'
                    },

                customize: function (doc) {

                doc.content.splice(0,1);

                doc.pageMargins = [15,55,15,25];

                // Smaller font for many columns
                doc.defaultStyle.fontSize = 7;

                var table = doc.content[0].table;

                // Auto column widths (important)
                table.widths = new Array(table.body[0].length).fill('*');

                // Better header style
                doc.styles.tableHeader = {
                    fillColor:'#2c3e50',
                    color:'white',
                    alignment:'center',
                    bold:true,
                    fontSize:8
                };

                var body = table.body;

                for (var i = 1; i < body.length; i++) {

                    body[i][0].alignment='center';
                    body[i][1].alignment='center';
                    body[i][2].alignment='center';
                    body[i][3].alignment='center';
                    body[i][4].alignment='left';

                    for (var j = 5; j < body[i].length; j++) {
                        body[i][j].alignment='right';
                    }
                }

                // Table layout for clean lines
                doc.content[0].layout = {
                    hLineWidth: function(){ return .5; },
                    vLineWidth: function(){ return .5; },
                    hLineColor: function(){ return '#aaa'; },
                    vLineColor: function(){ return '#aaa'; },
                    paddingLeft: function(){ return 4; },
                    paddingRight: function(){ return 4; }
                };

                // Header
                doc.content.unshift({

                    margin:[0,0,0,12],

                    columns:[
                        {
                            width:'33%',
                            columns:[
                                {image: pdfLogo, width:30},
                                {
                                    text:'LiquorHub',
                                    fontSize:11,
                                    bold:true,
                                    margin:[5,8,0,0]
                                }
                            ]
                        },

                        {
                            width:'34%',
                            text:'Discount & Offer Report',
                            alignment:'center',
                            fontSize:14,
                            bold:true,
                            margin:[0,8,0,0]
                        },

                        {
                            width:'33%',
                            text:'Generated: '+new Date().toLocaleString(),
                            alignment:'right',
                            fontSize:8,
                            margin:[0,8,0,0]
                        }

                    ]
                });

            }

                }

            ]
        }

        ],

        initComplete:function(){

            $('.dataTables_filter input').attr("placeholder","Search List...");

            $('#branch_id, #party_user_id, #payment_mode, #start_date, #end_date')
            .on('change',function(){
                table.ajax.reload();
            });

        }

    });

});


function getBase64Image(url,callback){

    var img = new Image();
    img.crossOrigin="Anonymous";

    img.onload=function(){

        var canvas=document.createElement("canvas");
        canvas.width=this.width;
        canvas.height=this.height;

        var ctx=canvas.getContext("2d");
        ctx.drawImage(this,0,0);

        var dataURL=canvas.toDataURL("image/png");

        callback(dataURL);

    };

    img.src=url;

}

getBase64Image("https://liquorhub.in/assets/images/logo.png",function(base64){
    pdfLogo=base64;
});

</script>
@endsection
