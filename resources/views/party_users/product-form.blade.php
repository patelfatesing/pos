@extends('layouts.backend.layouts')
<style>
    #productTable th,
    #productTable td {
        font-size: 15px; 
    }

    .auto-width-select {
        display: inline-block;
    }

    /* Custom visible scrollbar for table box */
    .scrollable-content {
        max-height: 450px;
        overflow-y: auto;
        position: relative;
        scrollbar-width: auto;
        scrollbar-color: #007bff #f1f1f1;
    }

    .scrollable-content::-webkit-scrollbar {
        width: 14px; 
    }

    .scrollable-content::-webkit-scrollbar-track {
        background: #f1f5f9; 
        border-radius: 6px;
    }

    .scrollable-content::-webkit-scrollbar-thumb {
        background: #007bff;
        border-radius: 6px;
        border: 2px solid #f1f5f9; 
    }

    .scrollable-content::-webkit-scrollbar-thumb:hover {
        background: #0056b3; 
    }

    #scrollToTop i {
        line-height: unset;
    }
</style>
@section('page-content')
    <div class="content-page">
        <div class="container-fluid add-form-list">
            <div class="row align-items-center mb-2">
                <div class="col-lg-12">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0">⚡ Party Customer Price Change -</h4>
                        </div>
                        <div class="d-flex align-items-center">
        <form action="" method="GET" class="mb-0 mr-2">
            <select name="id" id="id" class="form-control auto-width-select" onchange="this.form.submit()">
                @foreach ($partyUserAll as $party)
                    <option value="{{ $party->id }}" {{ $partyUser->id == $party->id ? 'selected' : '' }}>{{ trim($party->first_name) }}</option>
                @endforeach
            </select>

            @if (request()->has('subcategory_id') && !empty(request()->get('subcategory_id')))
                <input type="hidden" name="subcategory_id"
                    value="{{ request()->get('subcategory_id') }}">
            @endif
        </form>

        <a href="{{ route('party-users.list') }}" class="btn btn-secondary text-nowrap">Back</a>
    </div>
</div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="{{ route('party-users.cust-product-price-change-form') }}" class="mb-2">
                                <div class="row ml-2 mt-2 align-items-center">
                                    <div class="col-md-3">
                                        <!-- 🔍 Search box -->
                                        <input type="text" id="productSearch" class="form-control"
                                            placeholder="🔍 Search Product by Name or MRP" name="search">
                                    </div>

                                    <div class="col-md-3">
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

                                    <div class="col-md-1">
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
                                                    <th class="text-center" style="width: 5%;">#</th>
                                                    <th style="width: 55%;">Product</th>
                                                    <th class="text-center" style="width: 12%;">MRP</th>
                                                    <th class="text-center" style="width: 13%;">Sell Price</th>
                                                    <th class="text-center" style="width: 15%;">Discount Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($products as $key => $product)
                                                    <tr>
                                                        <td class="text-center">{{ $key + 1 }}</td>
                                                        <td>{{ $product->name }}</td>
                                                        <td class="text-center">{{ number_format($product->mrp, 0) }}</td>
                                                        <td class="text-center">{{ number_format($product->sell_price, 0) }}</td>
                                                        <td class="text-center">
                                                            <input type="number" step="0.1"
                                                                name="items[{{ $product->id }}][cust_discount_price]"
                                                                value="{{ old(
                                                                    'items.' . $product->id . '.cust_discount_price',
                                                                    $product->cust_discount_price == 0 ? (float) $product->mrp : (float) $product->cust_discount_price,
                                                                ) }}"
                                                                class="form-control discount-input mx-auto text-center @error('items.' . $product->id . '.cust_discount_price') is-invalid @enderror"
                                                                placeholder="Enter discount price">

                                                            <input type="hidden" name="items[{{ $product->id }}][mrp]"
                                                                value="{{ $product->mrp }}">

                                                            @error('items.' . $product->id . '.cust_discount_price')
                                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                            @enderror
                                                            @error('items.' . $product->id . '.sell_price')
                                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">No data available</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <a href="#" id="scrollToTop"><i class="fas fa-arrow-up"></i></a>

                                <!-- Notes -->
                                <!-- <div class="form-group mt-3">
                                    <label for="notes">Notes</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Enter Notes">
                                </div> -->

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


    <style>
        .scrollable-content {
            max-height: 450px;
            overflow-y: auto;
            position: relative;
        }

        .table td {
            padding-top: 4px !important;
            padding-bottom: 4px !important;
            padding-left: 8px !important;
            padding-right: 8px !important;
            vertical-align: middle !important;
        }

        .table td .form-control {
            height: 32px !important;
            padding: 2px 8px !important;
            font-size: 14px;
        }

        /* Optional: Styling for sort indicators */
        th.sorted-asc::after {
            content: ' ↑';
        }

        th.sorted-desc::after {
            content: ' ↓';
        }

        #scrollToTop {
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            text-align: center;
            line-height: 42px;
            color: #007bff;
            z-index: 1050;
            transition: background 0.3s, color 0.3s;
            text-decoration: none;
            display: none;
        }

        #scrollToTop:hover {
            background: #007bff;
            color: white;
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

        $(document).ready(function() {
            var $tableBox = $('.scrollable-content');

            function toggleScrollBtn() {
                var divScrolled = $tableBox.scrollTop() > 100;
                var pageScrolled = $(window).scrollTop() > $tableBox.offset().top - 100;
                if (divScrolled || pageScrolled) {
                    $('#scrollToTop').fadeIn();
                } else {
                    $('#scrollToTop').fadeOut();
                }
            }

            $tableBox.on('scroll', toggleScrollBtn);
            $(window).on('scroll', toggleScrollBtn);

            $('#scrollToTop').on('click', function(e) {
                e.preventDefault();

                $tableBox.animate({
                    scrollTop: 0
                }, 'slow');

                $('html, body').animate({
                    scrollTop: $tableBox.offset().top - 20
                }, 'slow');
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('id');
            if (!select) return;

            function adjustSelectWidth() {
                const tempSpan = document.createElement('span');
                const selectedText = select.options[select.selectedIndex]?.text || '';

                // Current select style copy karva mate
                const style = window.getComputedStyle(select);
                tempSpan.style.font = style.font;
                tempSpan.style.fontSize = style.fontSize;
                tempSpan.style.fontWeight = style.fontWeight;
                tempSpan.style.visibility = 'hidden';
                tempSpan.style.whiteSpace = 'nowrap';
                tempSpan.style.position = 'absolute';
                tempSpan.innerText = selectedText;

                document.body.appendChild(tempSpan);
                // Text width + padding (arrow space)
                select.style.width = (tempSpan.offsetWidth + 45) + 'px';
                document.body.removeChild(tempSpan);
            }

            // Page load par and option change thava par adjust thase
            adjustSelectWidth();
            select.addEventListener('change', adjustSelectWidth);
        });
    </script>
@endsection