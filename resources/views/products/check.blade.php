@extends('layouts.backend.layouts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
@section('page-content')
<style>
    #camera video{
        width:100%;
        max-width: 640px;
    }
</style>
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
                                <div id="camera" style="width:100%"></div>
                                <script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2/dist/quagga.min.js"></script>
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
        const quaggaConf = {
            inputStream: {
                target: document.querySelector("#camera"),
                type: "LiveStream",
                constraints: {
                    width: { min: 640 },
                    height: { min: 480 },
                    facingMode: "environment",
                    aspectRatio: { min: 1, max: 2 }
                }
            },
            decoder: {
                readers: ['code_128_reader']
            },
        }
    
        Quagga.init(quaggaConf, function (err) {
            if (err) {
                return console.log(err);
            }
            Quagga.start();
        });
    
        Quagga.onDetected(function (result) {
            alert("Detected barcode: " + result.codeResult.code);
        });
    </script>
    
@endsection
