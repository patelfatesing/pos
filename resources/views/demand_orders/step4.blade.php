@extends('layouts.backend.layouts')
<style>
    .custom-pdf-viewer {
        width: 100%;
        height: 900px;
    }
</style>
@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12 col-lg-12">
                        <div class="iq-card">
                            <div class="iq-card-header d-flex justify-content-between">
                                <div class="iq-header-title">
                                    <h4 class="card-title">Create Demand Order</h4>
                                </div>
                            </div>
                            <div class="iq-card-body">
                                <form id="form-wizard1" class="text-center mt-4">
                                    <ul id="top-tab-list" class="p-0">
                                        <li id="account">
                                            <a href="javascript:void();">
                                                <i class="ri-lock-unlock-line"></i><span>Search Details</span>
                                            </a>
                                        </li>
                                        <li id="personal">
                                            <a href="javascript:void();">
                                                <i class="ri-user-fill"></i><span>Prediction</span>
                                            </a>
                                        </li>
                                        <li id="payment">
                                            <a href="javascript:void();">
                                                <i class="ri-camera-fill"></i><span>Final Select</span>
                                            </a>
                                        </li>
                                        <li class="active" id="confirm">
                                            <a href="javascript:void();">
                                                <i class="ri-check-fill"></i><span>Finish</span>
                                            </a>
                                        </li>
                                    </ul>

                                    <fieldset>
                                        <div class="card shadow-sm border-0 ">

                                            <div class="" style="min-height: 700px;">
                                                <iframe src="{{ $pdfPath }}" class="rounded border custom-pdf-viewer"
                                                    allowfullscreen></iframe>
                                            </div>
                                          
                                        </div>
                                        <a href="{{ route('demand-order.step1') }}"
                                            class="btn btn-dark previous action-button-previous float-right mr-3">Complete</a>
                                        <a href="{{ $pdfPath }}" download
                                            class="btn btn-primary next action-button ">
                                            <i class="ri-download-2-line"></i> Download PDF
                                        </a>

                                    </fieldset>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Wrapper End -->
    @endsection
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
