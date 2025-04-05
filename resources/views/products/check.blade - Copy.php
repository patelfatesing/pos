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
                                    <h4 class="card-title">Edit User -  </h4>
                                </div>
                                <div>
                                    <a href="{{ route('users.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <h1>Scan Barcode using Webcam</h1>

                                <!-- Video stream for barcode scanning -->
                                <div id="scanner-container">
                                    <video id="barcode-scanner" width="400" height="300"></video>
                                </div>
                            
                                <p>Scanned Code: <span id="scanned-code">None</span></p>
                            
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
                        Quagga.ImageDebug.drawPath(box, {x: 0, y: 1}, ctx, {color: "green", lineWidth: 2});
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
                body: JSON.stringify({ code: scannedCode })
            })
            .then(response => response.json())
            .then(data => alert(data.message))
            .catch(error => console.error("Error:", error));
        });
    });
</script>

@endsection
