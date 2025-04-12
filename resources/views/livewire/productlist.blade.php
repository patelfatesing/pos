<div>
    {{-- @include('layouts.flash-message') --}}

    <main class="my-4">
        <div class="container mx-auto px-6">

            {{-- 🔍 Search Input --}}
            <div class="mb-4 d-flex flex-wrap gap-2">
                <input type="text" wire:model="searchInput" class="form-control" style="flex: 1 1 300px;" placeholder="Search for products..." 
                       wire:keydown.enter="doSearch">
                <button wire:click="doSearch" class="btn btn-outline-secondary">
                    <i class="ri-search-line"></i> Search
                </button>
                <button wire:click="resetSearch" class="btn btn-outline-danger">
                    Reset
                </button>
            </div>

            <div class="row">
                @forelse($products as $product)
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-img-top" style="height: 200px; background-size: cover; background-position: center; background-image: url('{{ asset('storage/' . $product->image) }}')"></div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-truncate">{{ $product->name }}</h5>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    @if(isset($product->inventorie))
                                        <p class="card-text mb-0 text-success fw-bold">₹{{ number_format($product->inventorie->sell_price, 2) }}</p>
                                    @endif
                                    {{-- Quantity Increment/Decrement Buttons --}}
                                    {{-- <div class="d-flex align-items-center">
                                        <button class="btn btn-sm btn-outline-success" wire:click="decrementQty({{ $product->id }})">−</button>
                                        <span class="mx-2">{{ $product->quantity }}</span>
                                        <button class="btn btn-sm btn-outline-warning" wire:click="incrementQty({{ $product->id }})">+</button>
                                    </div> --}}
                                </div>
                                <div class="mt-auto">
                                    <button class="btn btn-primary btn-sm w-100 mb-2" wire:click="addToCart({{ $product->id }})">
                                        Add to Cart
                                    </button>
                                    {{-- <a href="#" class="btn btn-link btn-sm w-100">View Details</a> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted text-center">No products found for "{{ $search }}"</p>
                    </div>
                @endforelse
            </div>
        </div>
    </main>
</div>
