<form id="custPriceChnageForm" action="{{ route('cust-product-price-change-store') }}" method="POST">
    @csrf

    <div class="modal-header">
        <h5 class="modal-title">⚡ Party Customer Price Change</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body p-0">
        <div class="scrollable-content p-3" tabindex="0" id="scrollableContent">
            <div class="container-fluid">
                <input type="hidden" name="cust_user_id" id="cust_user_id" value="{{ $partyUser->id }}" />

                <!-- 🔍 Search box -->
                <div class="form-group">
                    <input type="text" id="productSearch" class="form-control" placeholder="🔍 Search Product by Name or MRP">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="productTable">
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
                                        <input type="number" placeholder="Enter discount price"
                                            name="items[{{ $product->id }}][cust_discount_price]"
                                            value="{{ old('items.' . $product->id . '.cust_discount_price', $product->cust_discount_price == '0.00' ? $product->mrp : $product->cust_discount_price) }}"
                                            class="form-control @error('items.' . $product->id . '.cust_discount_price') is-invalid @enderror">

                                        <input type="hidden" name="items[{{ $product->id }}][sell_price]"
                                            value="{{ $product->mrp }}">

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
                                    <td colspan="5" class="text-center">✅ All products are above Low Level Stock.</td>
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
        console.log("📦 Customer Price Change Modal DOM Ready");

        const searchInput = document.getElementById('productSearch');
        const scrollable = document.getElementById('scrollableContent');

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            console.log("🔍 Search query:", query);

            const tableRows = document.querySelectorAll('#productTable tbody tr');

            tableRows.forEach(row => {
                const productName = row.cells[1]?.textContent.toLowerCase() || '';
                const productMrp = row.cells[2]?.textContent.toLowerCase() || '';
                const match = productName.includes(query) || productMrp.includes(query);

                row.style.display = match ? '' : 'none';
            });
        });

        // Focus behavior
        $('.modal').on('shown.bs.modal', function () {
            scrollable.focus();
        });
        scrollable.addEventListener('click', () => {
            scrollable.focus();
        });
    });
</script>
