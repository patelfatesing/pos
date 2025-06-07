<form id="custPriceChnageForm" action="{{ route('cust-product-price-change-store') }}" method="POST">
    @csrf

    <div class="modal-header">
        <h5 class="modal-title">⚡ Party Customer Price Change</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <!-- Scrollable Modal Body -->
    <div class="modal-body p-0">
        <div class="scrollable-content p-3" tabindex="0" id="scrollableContent">
            <div class="container-fluid">
                <input type="hidden" name="cust_user_id" id="cust_user_id" value="{{ $partyUser->id }}" />

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
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
                                    <td colspan="3" class="text-center">✅ All products are above Low Level Stock.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="form-group mt-3">
                    <label for="notes">Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Enter Notes">
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="submitButton" onclick="submitForm()">Submit Price Change</button>
    </div>
</form>

<style>
    .scrollable-content {
        max-height: 450px;
        overflow-y: auto;
    }

    .table th, .table td {
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
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;

                document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                if (data.success) {
                    alert(data.success);
                    location.reload();
                } else if (data.errors) {
                    for (const field in data.errors) {
                        const errorMessages = data.errors[field];
                        const fieldName = field.split('.');
                        let inputField = form.querySelector(
                            `[name="items[${fieldName[1]}][${fieldName[2]}]"]`
                        );

                        if (inputField) {
                            let errorDiv = document.createElement('div');
                            errorDiv.classList.add('invalid-feedback', 'd-block');
                            errorDiv.innerText = errorMessages.join(", ");
                            inputField.classList.add('is-invalid');
                            inputField.parentElement.appendChild(errorDiv);
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

    document.addEventListener('DOMContentLoaded', function () {
        const scrollable = document.getElementById('scrollableContent');

        // Auto-focus when modal is shown
        $('.modal').on('shown.bs.modal', function () {
            scrollable.focus();
        });

        // Allow focus again on click
        scrollable.addEventListener('click', () => {
            scrollable.focus();
        });
    });
</script>
