@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Stock Summary</h4>
                </div>
                <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
            </div>
            <!-- Page Header -->
            <div class="row mt-2">

                <!-- Branch Filter -->
                <div class="col-md-3 mb-2">
                    <select id="branch_filter" class="form-control">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-md-3 mb-2">
                    <input type="text" id="reportrange" class="form-control" />
                </div>

                <div class="col-md-2 mb-2">
                    <button id="reset-filters" class="btn btn-secondary">Reset</button>
                </div>

            </div>

            <!-- Table -->
            <div class="col-lg-12">
                <div class="table-responsive rounded mb-3">
                    <table class="table table-striped table-bordered nowrap" id="stock-table">
                        <div class="container mt-3">
                            <thead>
                                <tr>
                                    <th>Store</th>

                                    <th>Total Amount</th>
                                </tr>
                            </thead>

                            <tbody id="storeData">
                                @include('sales.partials.store-data')
                            </tbody>


                    </table>
                </div>
            </div>

        </div>
    </div>


    <!-- MODAL -->
    <div class="modal fade" id="verifyModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5>Verify Sale</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="modalContent">
                    Loading...
                </div>

            </div>
        </div>
    </div>

    <!-- Shift Summary Modal -->
    <div class="modal fade" id="shiftSummaryModal" tabindex="-1" aria-labelledby="shiftSummaryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="shiftSummaryModalLabel">Shift Close Summary - <span
                            id="modalBranchName">Branch</span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body row" id="shiftSummaryContent">

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $('#reportrange').daterangepicker({
                startDate: moment(),
                endDate: moment(),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

        });

        // ✅ OPEN VERIFY POPUP
        function openVerifyModal(id) {

            $('#verifyModal').modal('show');
            $('#modalContent').html('Loading...');

            $.get('/sale/verify/' + id, function(data) {
                $('#modalContent').html(data);
            });
        }

        $(document).ready(function() {

            // Date Picker
            $('#reportrange').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            // 🔥 LOAD DATA
            function loadData() {

                $.ajax({
                    url: "{{ route('sales.salas-report') }}",
                    type: "GET",
                    data: {
                        branch_id: $('#branch_filter').val(),
                        date_range: $('#reportrange').val(),
                        shift_id: window.selectedShiftId
                    },
                    success: function(data) {
                        $('#storeData').html(data);
                    }
                });
            }

            // 🔥 FILTER EVENTS
            $('#branch_filter').change(loadData);
            $('#reportrange').on('apply.daterangepicker', loadData);

            $('#reset-filters').click(function() {
                $('#branch_filter').val('');
                $('#reportrange').val('');
                window.selectedShiftId = '';
                loadData();
            });

            // 🔥 ROW TOGGLE
            $(document).on('click', '.store-row', function() {

                let id = $(this).data('id');
                $('#sales-' + id).toggleClass('d-none');
            });

            // 🔥 VIEW BUTTON (EXPAND + OPTIONAL POPUP)
            $(document).on('click', '.view-row', function(e) {

                e.stopPropagation();

                let id = $(this).data('id');

                // expand row
                $('#sales-' + id).removeClass('d-none');

            });

        });

        $(document).on('click', '.open-shift', function(e) {

            e.stopPropagation(); // prevent double trigger

            let storeId = $(this).data('shift');

            // ✅ 1. EXPAND ROW
            $('#sales-' + storeId).removeClass('d-none');

            // OR toggle:
            // $('#sales-' + storeId).toggleClass('d-none');

            // ✅ 2. OPEN SHIFT MODAL (YOUR EXISTING CODE)
            $.ajax({
                url: '{{ url('shift-manage/close-shift') }}/' + storeId,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {

                    if (response.code != 200) {
                        Swal.fire('Info', response.message, 'info');
                    } else {

                        $('#shiftSummaryContent').html(response.html);

                        const modal = new bootstrap.Modal(
                            document.getElementById('shiftSummaryModal')
                        );
                        modal.show();
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to load shift.', 'error');
                }
            });

        });
    </script>
@endsection
