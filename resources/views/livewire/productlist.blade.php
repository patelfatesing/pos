<div>
    @include('layouts.flash-message')

    <main class="my-4">
        <div class="container mx-auto px-6">
            <h3 class="text-gray-800 text-3xl font-bold mb-4">Our Products</h3>
            <div class="row">
                @foreach($products as $product)
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <div class="card-img-top" style="height: 200px; background-size: cover; background-position: center; background-image: url('{{ $product->image }}')"></div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">${{ $product->price }}</p>
                            <button class="btn btn-primary btn-sm" wire:click="addToCart({{ $product->id }})">
                                Add to Cart
                            </button>
                            <a href="#" class="btn btn-link btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </main>
</div>
