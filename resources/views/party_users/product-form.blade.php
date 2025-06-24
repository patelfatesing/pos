@extends('layouts.backend.layouts')

@section('page-content')
<div class="wrapper">
    <div class="content-page">
        <div class="container-fluid add-form-list">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title">⚡ Party Customer Price Change</h4>
                            <a href="{{ route('party-users.list') }}" class="btn btn-secondary">Back</a>
                        </div>

                        <div class="card-body">
                            <form id="custPriceChnageForm" action="{{ route('cust-product-price-change-store') }}" method="POST">
                                @csrf

                                <input type="hidden" name="cust_user_id" value="{{ $partyUser->id }}">

                                <!-- 🔍 Search box -->
                                <div class="form-group">
                                    <input type="text" id="productSearch" class="form-control" placeholder="🔍 Search Product by Name or MRP">
                                </div>

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
                                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                            @enderror
                                                            @error('items.' . $product->id . '.sell_price')
                                                                <div class="invalid-feedback d-block">{{ $message }}</div>
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
                                </div>

                                <!-- Notes -->
                                <div class="form-group mt-3">
                                    <label for="notes">Notes</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Enter Notes">
                                </div>

                                <!-- Submit Buttons -->
                                <div class="text-right mt-3">
                                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
                                    <button type="button" class="btn btn-primary" id="submitButton" onclick="submitForm()">Submit Price Change</button>
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

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('productSearch');
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#productTable tbody tr');

            rows.forEach(row => {
                const name = row.cells[1]?.textContent.toLowerCase() || '';
                const mrp = row.cells[2]?.textContent.toLowerCase() || '';
                row.style.display = (name.includes(query) || mrp.includes(query)) ? '' : 'none';
            });
        });
    });
</script>
@endsection
