<form id="custPriceChnageForm" action="{{ route('cust-product-price-change-store') }}" method="POST">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">⚡ Party Customer Price Change</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="container">
            <h5 class="mb-3"></h5>
            <input type="hidden" name="cust_user_id" id="cust_user_id" value="{{ $partyUser->id }}" />
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Sell Price</th>
                        <th>Discount Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->sell_price }}</td>
                            <td>
                                <input type="number" placeholder="Enter discount price"
                                    name="items[{{ $product->id }}][cust_discount_price]"
                                    value="{{ old('items.' . $product->id . '.cust_discount_price', $product->cust_discount_price == '0.00' ? $product->sell_price : $product->cust_discount_price) }}"
                                    class="form-control @error('items.' . $product->id . '.cust_discount_price') is-invalid @enderror">

                                <input type="hidden" name="items[{{ $product->id }}][sell_price]"
                                    value="{{ $product->sell_price }}">
                                @error('items.' . $product->id . '.cust_discount_price')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                                @error('items.' . $product->id . '.sell_price')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">✅ All products are above Low Level Stock.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <input type="text" name="notes" class="form-control" placeholder="Enter Notes">
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="submitButton" onclick="submitForm()">Submit Price
            Change</button> <!-- Call JS function -->
    </div>
</form>
<script>
    function submitForm() {
        const form = document.getElementById('custPriceChnageForm');
        const submitButton = document.getElementById('submitButton');

        submitButton.disabled = true; // Disable the submit button to prevent multiple submissions

        // Prepare the form data
        let formData = new FormData(form);

        // Send AJAX request using fetch API
        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Indicates AJAX request
                }
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false; // Re-enable the submit button

                // Clear previous error messages
                document.querySelectorAll('.invalid-feedback').forEach((el) => el.remove());
                document.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));

                // Handle success response
                if (data.success) {
                    alert(data.success);
                    location.reload(); // Or close the modal
                }

                // Handle validation errors (if any)
                else if (data.errors) {
                    for (const field in data.errors) {
                        // Get the field name (e.g., items.1.cust_discount_price)
                        const errorMessages = data.errors[field];
                        const fieldName = field.split('.'); // Split the field name into parts
                        let inputField = form.querySelector(
                            `[name="items[${fieldName[1]}][${fieldName[2]}]"]`
                        ); // Build the query selector for nested fields

                        if (inputField) {
                            // Create the error message div
                            let errorDiv = document.createElement('div');
                            errorDiv.classList.add('invalid-feedback', 'd-block');
                            errorDiv.innerText = errorMessages.join(", ");

                            // Add the 'is-invalid' class to the input field
                            inputField.classList.add('is-invalid');

                            // Append the error message below the input field
                            inputField.parentElement.appendChild(errorDiv);
                        }
                    }
                }

                // Handle general errors (non-validation errors)
                else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(error => {
                submitButton.disabled = false; // Re-enable submit button on error
                alert('An error occurred: ' + error.message);
            });
    }
</script>
