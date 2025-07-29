@extends('layouts.backend.layouts')

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <!-- Title and Dropdown on the same line -->
                                    <h4 class="card-title mb-0 d-flex align-items-center">
                                        ⚡ Party Customer Price Change -
                                        <form class="ms-3 ml-2 m-3" action="" method="GET" style="display: inline;">
                                            <select name="id" id="id" class="form-control"
                                                onchange="this.form.submit()">
                                                <option value="">All Subcategories</option>
                                                @foreach ($partyUserAll as $party)
                                                    <option value="{{ $party->id }}"
                                                        {{ $partyUser->id == $party->id ? 'selected' : '' }}>
                                                        {{ $party->first_name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @if (request()->has('subcategory_id') && !empty(request()->get('subcategory_id')))
                                                <input type="hidden" name="subcategory_id"
                                                    value="{{ request()->get('subcategory_id') }}">
                                            @endif
                                        </form>
                                    </h4>
                                    <a href="{{ route('party-users.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>

                            <div class="card-body">
                                <form method="GET" action="{{ route('party-users.cust-product-price-change-form') }}">
                                    <div class="row ml-2 mt-2">
                                        <div class="col-md-3 mb-2">
                                            <!-- 🔍 Search box -->
                                            <div class="form-group">
                                                <input type="text" id="productSearch" class="form-control"
                                                    placeholder="🔍 Search Product by Name or MRP" name="search">
                                            </div>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <select name="subcategory_id" id="subcategory_id" class="form-control"
                                                onchange="this.form.submit()">
                                                <option value="">All Subcategories</option>
                                                @foreach ($subcategories as $subcategory)
                                                    <option value="{{ $subcategory->id }}"
                                                        {{ request('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                                        {{ $subcategory->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-1 mb-2">
                                            <!-- Reset button that keeps the `id` parameter intact -->
                                            <a href="{{ route('party-users.cust-product-price-change-form') }}?id={{ $partyUser->id }}"
                                                class="btn btn-secondary">Reset</a>
                                        </div>
                                    </div>

                                    <!-- Hidden input to include the `id` parameter in the form submission -->
                                    <input type="hidden" name="id" value="{{ $partyUser->id }}">
                                </form>

                                <form id="custPriceChnageForm" action="{{ route('cust-product-price-change-store') }}"
                                    method="POST">
                                    @csrf

                                    <input type="hidden" name="cust_user_id" value="{{ $partyUser->id }}">



                                    <!-- Product Table -->
                                    <div class="scrollable-content p-3 border rounded" id="scrollableContent">
                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-0" id="productTable">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Product</th>
                                                        <th>MRP</th>
                                                        <th>Sell Price</th>
                                                        <th>Discount Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($products as $key => $product)
                                                        <tr>
                                                            <td>{{ $key + 1 }}</td>
                                                            <td>{{ $product->name }}</td>
                                                            <td>{{ $product->mrp }}</td>
                                                            <td>{{ $product->sell_price }}</td>
                                                            <td>
                                                                <input type="number" step="0.01"
                                                                    name="items[{{ $product->id }}][cust_discount_price]"
                                                                    value="{{ old('items.' . $product->id . '.cust_discount_price', $product->cust_discount_price == '0.00' ? $product->mrp : $product->cust_discount_price) }}"
                                                                    class="form-control @error('items.' . $product->id . '.cust_discount_price') is-invalid @enderror"
                                                                    placeholder="Enter discount price">

                                                                <input type="hidden"
                                                                    name="items[{{ $product->id }}][mrp]"
                                                                    value="{{ $product->mrp }}">

                                                                @error('items.' . $product->id . '.cust_discount_price')
                                                                    <div class="invalid-feedback d-block">{{ $message }}
                                                                    </div>
                                                                @enderror
                                                                @error('items.' . $product->id . '.sell_price')
                                                                    <div class="invalid-feedback d-block">{{ $message }}
                                                                    </div>
                                                                @enderror
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center">✅ All products are above
                                                                Low Level Stock.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div class="form-group mt-3">
                                        <label for="notes">Notes</label>
                                        <input type="text" name="notes" class="form-control" placeholder="Enter Notes">
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="text-right mt-3">
                                        <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
                                        <button type="button" class="btn btn-primary" id="submitButton"
                                            onclick="submitForm()">Submit Price Change</button>
                                    </div>
                                </form>
                            </div> <!-- /.card-body -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .scrollable-content {
            max-height: 450px;
            overflow-y: auto;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }
    </style>

    <script>
        function submitForm() {
            const form = document.getElementById('custPriceChnageForm');
            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = true;

            let formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    submitButton.disabled = false;

                    // Clear previous errors
                    document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                    if (data.success) {
                        alert(data.success);
                        window.location.reload();
                    } else if (data.errors) {
                        for (const field in data.errors) {
                            const messages = data.errors[field];
                            const parts = field.split('.');
                            const productId = parts[1];
                            const inputName = parts[2];

                            const input = form.querySelector(`[name="items[${productId}][${inputName}]"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.classList.add('invalid-feedback', 'd-block');
                                errorDiv.innerText = messages.join(', ');
                                input.parentElement.appendChild(errorDiv);
                            }
                        }
                    } else if (data.error) {
                        alert(data.error);
                    }
                })
                .catch(error => {
                    submitButton.disabled = false;
                    alert('An error occurred: ' + error.message);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('productSearch');
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#productTable tbody tr');

                rows.forEach(row => {
                    const name = row.cells[1]?.textContent.toLowerCase() || '';
                    const mrp = row.cells[2]?.textContent.toLowerCase() || '';
                    row.style.display = (name.includes(query) || mrp.includes(query)) ? '' : 'none';
                });
            });
        });

        // Wait for the document to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Get the table element and its tbody (body rows)
            const table = document.getElementById('productTable');
            const tbody = table.querySelector('tbody');
            const headers = table.querySelectorAll('th');

            // Keep track of the current sort direction for each column
            let currentSortDirection = Array(headers.length).fill('asc');

            // Loop through each header to add sorting functionality
            headers.forEach((header, index) => {
                header.addEventListener('click', () => {
                    sortTable(index);
                });
            });

            // Function to sort the table by a specific column index
            function sortTable(columnIndex) {
                const rows = Array.from(tbody.rows); // Get rows from tbody

                const isNumericColumn = columnIndex === 2 || columnIndex ===
                    3; // Assuming 'MRP' and 'Sell Price' are numeric columns

                // Determine the sort direction (toggle between asc and desc)
                const sortDirection = currentSortDirection[columnIndex] === 'asc' ? 'desc' : 'asc';
                currentSortDirection[columnIndex] = sortDirection; // Update direction for the current column

                // Sort the rows
                const sortedRows = rows.sort((a, b) => {
                    const cellA = a.cells[columnIndex].innerText.toLowerCase();
                    const cellB = b.cells[columnIndex].innerText.toLowerCase();

                    if (isNumericColumn) {
                        // If the column is numeric, compare as numbers
                        return sortDirection === 'asc' ? parseFloat(cellA) - parseFloat(cellB) : parseFloat(
                            cellB) - parseFloat(cellA);
                    } else {
                        // If the column is a string, compare lexicographically
                        return sortDirection === 'asc' ? cellA.localeCompare(cellB) : cellB.localeCompare(
                            cellA);
                    }
                });

                // Append the sorted rows back to the table body
                sortedRows.forEach(row => tbody.appendChild(row));

                // Optionally, add an indicator for the current sort direction
                updateSortIndicator(columnIndex, sortDirection);
            }

            // Function to update the sort indicator (optional, can add arrows or styles to indicate the sort direction)
            function updateSortIndicator(columnIndex, sortDirection) {
                headers.forEach((header, idx) => {
                    // Remove any existing indicator
                    header.classList.remove('sorted-asc', 'sorted-desc');

                    // Add indicator based on sort direction
                    if (idx === columnIndex) {
                        header.classList.add(sortDirection === 'asc' ? 'sorted-asc' : 'sorted-desc');
                    }
                });
            }
        });
    </script>
@endsection
<style>
    /* Optional: Styling for sort indicators */
    th.sorted-asc::after {
        content: ' ↑';
    }

    th.sorted-desc::after {
        content: ' ↓';
    }
</style>
