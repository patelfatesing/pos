<div class="d-flex justify-content-between align-items-center mb-2">

    <h5 class="mb-0">Transfer List</h5>

    <!-- ✅ ADD TRANSFER BUTTON -->
    <button class="btn btn-success btn-sm" onclick="openCreateTransfer()">
        <i class="fa fa-plus"></i> Add Transfer
    </button>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped" id="transferModalTable">
        <thead>
            <tr>
                <th>Transfer #</th>
                <th>From</th>
                <th>To</th>
                <th>Products</th>
                <th>Total Qty</th>
                <th>Date</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
</div>
<input type="hidden" id="shift_id_1" value="">
<input type="hidden" id="shift_date_1" value="">

<script>
    function initTransferTable(branch_id, shift_id, type) {

        $("#shift_id_1").val(shift_id);
        $("#shift_date_1").val(branch_id);


        $('#transferModalTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true, // 🔥 VERY IMPORTANT

            ajax: {
                url: "{{ route('stock-transfer.get-transfer-data-new') }}",
                type: "GET",
                data: function(d) {
                    d.branch_id = branch_id;
                    d.shift_id = shift_id;
                    d.type = type;
                }
            },

            columns: [{
                    data: 'transfer_number'
                },
                {
                    data: 'from'
                },
                {
                    data: 'to'
                },
                {
                    data: 'total_products',
                    render: function(data) {
                        return data + ' item(s)';
                    }
                },
                {
                    data: 'total_quantity',
                    render: function(data) {
                        return data + ' units';
                    }
                },
                {
                    data: 'transferred_at'
                },
                {
                    data: 'status',
                    render: function(data) {
                        let cls = 'badge badge-';

                        if (data.toLowerCase() === 'completed') cls += 'success';
                        else if (data.toLowerCase() === 'pending') cls += 'warning';
                        else if (data.toLowerCase() === 'cancelled') cls += 'danger';
                        else cls += 'info';

                        return '<span class="' + cls + '">' + data + '</span>';
                    }
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ],

            order: [
                [5, 'desc']
            ],
            pageLength: 10
        });
    }
</script>
