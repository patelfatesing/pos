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
                                    <h4 class="card-title">View Product - {{ $product_details->name }} </h4>
                                </div>
                                <div>
                                    <a href="{{ route('products.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                        
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name *</label>
                                                <input type="text" class="form-control" disabled
                                                    value="{{ $product_details->name }}">
                                                @error('name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Brand *</label>
                                                <input type="text" class="form-control" disabled
                                                    value="{{ $product_details->brand }}" placeholder="Enter Brand">
                                                @error('brand')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>


                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>SKU *</label>
                                                <input type="text" class="form-control" disabled
                                                    value="{{ $product_details->sku }}" placeholder="Enter Brand">
                                                @error('brand')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Category *</label>
                                                <input type="text" disabled
                                                    value="{{ $product_details->category->name }}" class="form-control"
                                                    placeholder="Enter Category">
                                                @error('category')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Size</label>
                                                <input type="text" disabled value="{{ $product_details->size }}"
                                                    name="size" class="form-control" placeholder="Enter Size">
                                                @error('size')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Low Level Stock</label>
                                                <input type="number" name="reorder_level" value="{{old('reorder_level',$product_details->reorder_level) }}" class="form-control"
                                                    placeholder="Enter Low Level Stock">
                                                @error('reorder_level')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Cost Price *</label>
                                                <input type="number" step="0.01" value="{{old('cost_price',$product_details->cost_price) }}" name="cost_price" class="form-control"
                                                    placeholder="Enter Cost Price">
                                                @error('cost_price')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Sell Price *</label>
                                                <input type="number" step="0.01" value="{{old('sell_price',$product_details->sell_price) }}" name="sell_price" class="form-control"
                                                    placeholder="Enter Sell Price">
                                                @error('sell_price')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- Discount Price -->
                                            <div class="form-group">
                                                <label for="discount_price">Discount Price</label>
                                                <input type="number" value="{{old('discount_price',$product_details->discount_price) }}" name="discount_price" step="0.01"
                                                    class="form-control">

                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Barcode</label>
                                                <input type="text" name="barcode" id="barcode" value="{{old('barcode',$product_details->barcode) }}" class="form-control"
                                                    placeholder="Enter Code" data-errors="Please Enter barcode.">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Quantity *</label>
                                                <input type="number" name="quantity" class="form-control"
                                                    placeholder="Enter Quantity">
                                                @error('quantity')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- MFG Date -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="mfg_date">Manufacturing Date</label>
                                                <input type="date" name="mfg_date" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Expiry Date</label>
                                                <input type="date" name="expiry_date" class="form-control">
                                                @error('expiry_date')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="vendor_id">Vendor</label>
                                                <select name="vendor_id" id="vendor_id" class="form-control">
                                                    <option value="">-- Select Vendor --</option>
                                                    @foreach ($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                
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
