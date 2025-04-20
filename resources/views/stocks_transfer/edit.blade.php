@extends('layouts.backend.layouts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">
        <?php
        // dd($record->userInfo);
        ?>
        <div class="content-page">

            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Edit User - {{ $record->name }} </h4>
                                </div>
                                <div>
                                    <a href="{{ route('users.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('products.store') }}" enctype="multipart/form-data" method="POST">
                                    @csrf

                                    <div class="row">
                                        {{-- filepath: d:\xampp\htdocs\pos\resources\views\products\edit.blade.php --}}
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Product Type *</label>
                                                <select name="product_type" class="selectpicker form-control"
                                                    data-style="py-0">
                                                    <option value="Standard"
                                                        {{ $record->product_type == 'Standard' ? 'selected' : '' }}>
                                                        Standard</option>
                                                    <option value="Combo"
                                                        {{ $record->product_type == 'Combo' ? 'selected' : '' }}>Combo
                                                    </option>
                                                    <option value="Digital"
                                                        {{ $record->product_type == 'Digital' ? 'selected' : '' }}>Digital
                                                    </option>
                                                    <option value="Service"
                                                        {{ $record->product_type == 'Service' ? 'selected' : '' }}>Service
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name *</label>
                                                <input type="text" name="name" class="form-control"
                                                    placeholder="Enter Name" value="{{ $record->name }}">
                                                @error('name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Code *</label>
                                                <input type="text" name="code" class="form-control"
                                                    placeholder="Enter Code" value="{{ $record->code }}">
                                                @error('code')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Barcode Symbology *</label>
                                                <select name="barcode_symbology" class="selectpicker form-control"
                                                    data-style="py-0">
                                                    <option value="CREM01"
                                                        {{ $record->barcode_symbology == 'CREM01' ? 'selected' : '' }}>
                                                        CREM01</option>
                                                    <option value="UM01"
                                                        {{ $record->barcode_symbology == 'UM01' ? 'selected' : '' }}>UM01
                                                    </option>
                                                    <option value="SEM01"
                                                        {{ $record->barcode_symbology == 'SEM01' ? 'selected' : '' }}>SEM01
                                                    </option>
                                                    <option value="COF01"
                                                        {{ $record->barcode_symbology == 'COF01' ? 'selected' : '' }}>COF01
                                                    </option>
                                                    <option value="FUN01"
                                                        {{ $record->barcode_symbology == 'FUN01' ? 'selected' : '' }}>FUN01
                                                    </option>
                                                    <option value="DIS01"
                                                        {{ $record->barcode_symbology == 'DIS01' ? 'selected' : '' }}>DIS01
                                                    </option>
                                                    <option value="NIS01"
                                                        {{ $record->barcode_symbology == 'NIS01' ? 'selected' : '' }}>NIS01
                                                    </option>
                                                </select>
                                                @error('barcode_symbology')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Category *</label>
                                                <select name="category" class="selectpicker form-control"
                                                    data-style="py-0">
                                                    <option value="Beauty"
                                                        {{ $record->category == 'Beauty' ? 'selected' : '' }}>Beauty
                                                    </option>
                                                    <option value="Grocery"
                                                        {{ $record->category == 'Grocery' ? 'selected' : '' }}>Grocery
                                                    </option>
                                                    <option value="Food"
                                                        {{ $record->category == 'Food' ? 'selected' : '' }}>Food</option>
                                                    <option value="Furniture"
                                                        {{ $record->category == 'Furniture' ? 'selected' : '' }}>Furniture
                                                    </option>
                                                    <option value="Shoes"
                                                        {{ $record->category == 'Shoes' ? 'selected' : '' }}>Shoes</option>
                                                    <option value="Frames"
                                                        {{ $record->category == 'Frames' ? 'selected' : '' }}>Frames
                                                    </option>
                                                    <option value="Jewellery"
                                                        {{ $record->category == 'Jewellery' ? 'selected' : '' }}>Jewellery
                                                    </option>
                                                </select>
                                                @error('category')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Cost *</label>
                                                <input type="text" name="cost" class="form-control"
                                                    placeholder="Enter Cost" value="{{ $record->cost }}">
                                                @error('cost')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Price *</label>
                                                <input type="text" name="price" class="form-control"
                                                    placeholder="Enter Price" value="{{ $record->price }}">
                                                @error('price')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Tax Method *</label>
                                                <select name="tax_method" class="selectpicker form-control"
                                                    data-style="py-0">
                                                    <option value="Exclusive"
                                                        {{ $record->tax_method == 'Exclusive' ? 'selected' : '' }}>
                                                        Exclusive</option>
                                                    <option value="Inclusive"
                                                        {{ $record->tax_method == 'Inclusive' ? 'selected' : '' }}>
                                                        Inclusive</option>
                                                </select>
                                                @error('tax_method')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Quantity *</label>
                                                <input type="text" name="quantity" class="form-control"
                                                    placeholder="Enter Quantity" value="{{ $record->quantity }}">
                                                @error('quantity')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Image</label>
                                                <input type="file" name="image" class="form-control image-file"
                                                    accept="image/*">
                                                @if ($record->image)
                                                    <img src="{{ asset('storage/' . $record->image) }}"
                                                        alt="Product Image" width="100">
                                                @endif
                                                @error('image')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Description / Product Details</label>
                                                <textarea class="form-control" name="description" rows="4">{{ $record->description }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2">Add Product</button>
                                    <button type="reset" class="btn btn-danger">Reset</button>
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
        document.addEventListener("DOMContentLoaded", function() {
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    constraints: {
                        width: 640,
                        height: 480,
                        facingMode: "environment" // Use rear camera
                    },
                    target: document.querySelector("#scanner-container")
                },
                decoder: {
                    readers: ["code_128_reader", "ean_reader", "upc_reader"]
                },
                locate: true // Try to locate barcode automatically
            }, function(err) {
                if (err) {
                    console.error("QuaggaJS Init Error:", err);
                    return;
                }
                console.log("QuaggaJS Initialized Successfully");
                Quagga.start();
            });

            // Debug: Log barcode scanning process
            Quagga.onProcessed(function(result) {
                let drawingCanvas = Quagga.canvas.dom.overlay;
                let ctx = Quagga.canvas.ctx.overlay;

                if (result) {
                    if (result.boxes) {
                        ctx.clearRect(0, 0, drawingCanvas.width, drawingCanvas.height);
                        result.boxes.forEach(box => {
                            Quagga.ImageDebug.drawPath(box, {
                                x: 0,
                                y: 1
                            }, ctx, {
                                color: "green",
                                lineWidth: 2
                            });
                        });
                    }

                    if (result.codeResult && result.codeResult.code) {
                        console.log("Detected Barcode:", result.codeResult.code);
                    }
                }
            });

            // When a barcode is detected
            Quagga.onDetected(function(result) {
                let scannedCode = result.codeResult.code;
                document.getElementById("scanned-code").textContent = scannedCode;
                console.log("Barcode Scanned:", scannedCode);
                Quagga.stop();

                // Send scanned code to Laravel
                fetch('/products/barcode/check', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            code: scannedCode
                        })
                    })
                    .then(response => response.json())
                    .then(data => alert(data.message))
                    .catch(error => console.error("Error:", error));
            });
        });
    </script>
@endsection
