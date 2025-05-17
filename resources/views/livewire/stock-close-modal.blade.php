<div>
    <button type="button" class="btn btn-primary ml-2" wire:click.prevent="closingStocksOpenModal" title="Close Shift">
        <i class="fas fa-door-closed"></i>
    </button>
    @if ($showCloseModal)
        <div class="modal fade show d-block" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable modal-xl">
                <div class="modal-content shadow-sm rounded-4 border-0">

                    {{-- Modal Header --}}
                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <div class="d-flex flex-column">
                            <h5 class="modal-title fw-semibold">
                                <i class="bi bi-cash-coin me-2"></i> Shift Close Summary - {{ $branch_name ?? 'Shop' }}
                            </h5>
                        </div>


                    </div>

                    {{-- Modal Body --}}
                    <div class="modal-body px-4 py-4">
                        <div class="card mt-4">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">🧾 Product Stock Summary</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Product</th>
                                                <th>Opening Stock</th>
                                                <th>Added Stock</th>
                                                <th>Transferred Stock</th>
                                                <th>Sold Stock</th>
                                                <th>Closing Stock</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($productStock as $stock)
                                                <tr>
                                                    <td>{{ $stock->product->name ?? 'N/A' }}</td>
                                                    <td>{{ $stock->opening_stock }}</td>
                                                    <td>{{ $stock->added_stock }}</td>
                                                    <td>{{ $stock->transferred_stock }}</td>
                                                    <td>{{ $stock->sold_stock }}</td>
                                                    <td>{{ $stock->closing_stock }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No stock data
                                                        available.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        {{-- Modal backdrop --}}
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
