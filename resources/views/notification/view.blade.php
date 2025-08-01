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
                                    <div class="header-title">
                                        <h4 class="card-title">Expire Product</h4>
                                    </div>
                                    
                                </div>
                                <div class="card-body">

                                    <!-- Show Entries Form -->
                                    <!-- Show Entries + Search Form Row -->
                                    <form method="GET" id="perPageForm" class="mb-3">

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
                                                    <option value="{{ $expiredProducts->total() }}"
                                                        {{ $perPage == $expiredProducts->total() ? 'selected' : '' }}>All</option>
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
                                                    <th>Sr No</th>
                                                    <th>Product Name</th>
                                                    <th>Batch No</th>
                                                    <th>Expiry Date</th>
                                                    <th>Quantity</th>
                                                    <th>Barcode</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($expiredProducts as $key => $invoice)
                                                    <tr>
                                                        <td>
                                                            {{ $key + 1 }}
                                                        </td>
                                                        <td>{{ $invoice->product_name }}</td>
                                                        <td>{{ $invoice->batch_no }}
                                                        </td>
                                                        <td>{{ $invoice->expiry_date }}</td>
                                                        <td>{{ $invoice->quantity }}</td>
                                                        <td>{{ $invoice->barcode }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                </tr>
                                            </tfoot>
                                        </table>

                                        <!-- Pagination -->
                                        <div class="d-flex justify-content-center mt-3">
                                            {{ $expiredProducts->appends(['per_page' => $perPage])->links('pagination::bootstrap-5') }}
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
