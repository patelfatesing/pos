@extends('layouts.backend.layouts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">

            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Stock Request</h4>
                                </div>
                                <div>
                                    <a href="{{ route('products.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>

                            <div class="card-body">

                                <form method="POST" action="{{ route('stock.warehouse') }}">
                                    @csrf

                                    {{-- filepath: d:\xampp\htdocs\pos\resources\views\stocks\create.blade.php --}}


                                    <div id="product-items">
                                        <h5>Products</h5>
                                        <div class="item-row product_items mb-3">
                                            <select name="items[0][product_id]"
                                                class="form-control d-inline w-50 product-select" required>
                                                <option value="">-- Select Product --</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}
                                                        ({{ $product->sku }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="number" name="items[0][quantity]"
                                                class="form-control d-inline w-25 ms-2" placeholder="Qty" min="1"
                                                required>
                                            <button type="button" class="btn btn-danger btn-sm ms-2 remove-item">X</button>
                                            <div class="availability-container mt-2 small text-muted">
                                                <!-- Filled dynamically with AJAX -->
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        {{-- filepath: d:\xampp\htdocs\pos\resources\views\stocks\create.blade.php --}}
                                        <div id="product-availability" class="mt-3">
                                            <!-- Availability information will be displayed here -->
                                        </div>


                                    </div>



                                    <button type="button" id="add-item" class="btn btn-secondary btn-sm mb-3">+ Add
                                        Another Product</button>

                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea name="notes" id="notes" class="form-control"></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Submit Request</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Wrapper End-->
    <script>
        let itemIndex = 1;
    
        document.getElementById('add-item').addEventListener('click', function () {
            // Clone the first item-row
            const row = document.querySelector('.item-row').cloneNode(true);
    
            // Update the name attributes for the cloned row
            row.querySelectorAll('select, input').forEach(el => {
                const name = el.getAttribute('name');
                const updatedName = name.replace(/\[\d+\]/, `[${itemIndex}]`);
                el.setAttribute('name', updatedName);
    
                // Clear the value for inputs
                if (el.tagName === 'INPUT') el.value = '';
            });
    
            // Clear the availability-container for the cloned row
            const container = row.querySelector('.availability-container');
            container.innerHTML = '';
    
            // Append the cloned row to the product-items container
            document.getElementById('product-items').appendChild(row);
    
            // Increment the item index
            itemIndex++;
        });
    
        document.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-item')) {
                if (document.querySelectorAll('.item-row').length > 1) {
                    e.target.closest('.item-row').remove();
                }
            }
        });
    
        $(document).ready(function () {
            // Event listener for product selection change
            $(document).on('change', '.product-select', function () {
                const productId = $(this).val();
                const itemRow = $(this).closest('.item-row');
                const container = itemRow.find('.availability-container');
                const indexMatch = $(this).attr('name').match(/\[(\d+)\]/);
                const itemIndex = indexMatch ? indexMatch[1] : 0;
    
                if (productId) {
                    // AJAX request to fetch product availability
                    $.ajax({
                        url: "{{ url('/products/availability') }}/" + productId,
                        type: "GET",
                        dataType: "json",
                        success: function (data) {
                            // console.log(data);
    
                            let html = `<div class="row">`;
    
                            $.each(data, function (branchName, availableQty,id) {
                                console.log(branchName,'1');
                                console.log(availableQty,'22');
                                console.log(id,'33');
                                html += `
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input branch-checkbox" type="checkbox"
                                                   name="items[${itemIndex}][branches][${availableQty.id}]"
                                                   id="branch_${itemIndex}_${availableQty.id}">
                                            <label class="form-check-label" for="branch_${itemIndex}_${availableQty.id}">
                                                ${availableQty.name} (Available: ${availableQty.available_quantity})
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <input type="number" name="items[${itemIndex}][branch_quantities][${availableQty.id}]"
                                               class="form-control branch-quantity" min="1"
                                               placeholder="Qty for ${availableQty.name}" disabled>
                                    </div>
                                `;
                            });
    
                            html += '</div>';
                            container.html(html);
                        },
                        error: function () {
                            container.html(
                                '<span class="text-danger">Failed to load availability. Please try again.</span>'
                            );
                        }
                    });
                } else {
                    container.empty(); // Clear container if no product is selected
                }
            });
    
            // Enable/disable quantity input based on checkbox selection
            $(document).on('change', '.branch-checkbox', function () {
                const quantityInput = $(this).closest('.col-md-6').next('.col-md-6').find('.branch-quantity');
                if ($(this).is(':checked')) {
                    quantityInput.prop('disabled', false);
                } else {
                    quantityInput.prop('disabled', true).val(''); // Clear value when disabled
                }
            });
    
            // Validate branch quantities
            $(document).on('input', '.branch-quantity', function () {
                const itemRow = $(this).closest('.item-row');
                const totalRequestedQty = parseInt(itemRow.find('input[name$="[quantity]"]').val()) || 0;
                let totalBranchQty = 0;
    
                // Calculate the total quantity across all branches
                itemRow.find('.branch-quantity').each(function () {
                    const branchQty = parseInt($(this).val()) || 0;
                    totalBranchQty += branchQty;
                });
    
                // Check if the total branch quantity exceeds the requested quantity
                if (totalBranchQty > totalRequestedQty) {
                    alert('The total quantity across branches cannot exceed the requested quantity.');
                    $(this).val(''); // Clear the invalid input
                }
            });
        });


 // Validate branch quantities
 $(document).on('input', '.branch-quantity', function () {
            const itemRow = $(this).closest('.item-row');
            const totalRequestedQty = parseInt(itemRow.find('input[name$="[quantity]"]').val()) || 0;
            let totalBranchQty = 0;

            // Calculate the total quantity across all branches
            itemRow.find('.branch-quantity').each(function () {
                const branchQty = parseInt($(this).val()) || 0;
                totalBranchQty += branchQty;
            });

            // Check if the total branch quantity exceeds the requested quantity
            if (totalBranchQty > totalRequestedQty) {
                alert('The total quantity across branches cannot exceed the requested quantity.');
                $(this).val(''); // Clear the invalid input
            }
        });

        // Form submission validation
        $('form').on('submit', function (e) {
            let isValid = true;
            let hasSelectedProduct = false;

            // Validate each product row
            $('.item-row').each(function () {
                const productSelect = $(this).find('.product-select');
                const quantityInput = $(this).find('input[name$="[quantity]"]');
                const totalRequestedQty = parseInt(quantityInput.val()) || 0;

                // Check if a product is selected
                if (productSelect.val()) {
                    hasSelectedProduct = true; // At least one product is selected
                }

                // Check if quantity is valid
                if (productSelect.val() && totalRequestedQty <= 0) {
                    alert('Please enter a valid quantity for all products.');
                    isValid = false;
                    return false; // Break out of the loop
                }

                // Validate branch quantities
                let totalBranchQty = 0;
                $(this).find('.branch-quantity').each(function () {
                    const branchQty = parseInt($(this).val()) || 0;
                    if ($(this).prop('disabled') === false && branchQty <= 0) {
                        alert('Branch quantities must be greater than zero.');
                        isValid = false;
                        return false; // Break out of the loop
                    }
                    totalBranchQty += branchQty;
                });

                if (totalBranchQty > totalRequestedQty) {
                    alert('The total quantity across branches cannot exceed the requested quantity.');
                    isValid = false;
                    return false; // Break out of the loop
                }
            });

            // Check if at least one product is selected
            if (!hasSelectedProduct) {
                alert('Please select at least one product.');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault(); // Prevent form submission if validation fails
            }
        });
    </script>
@endsection
