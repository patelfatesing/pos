@extends('layouts.backend.layouts')

@section('page-content')

    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Edit User - {{ $product_details->name }} </h4>
                                </div>
                                <div>
                                    <button onclick="printSection('printable-area')">🖨️ Print</button>
                                    <a href="{{ route('users.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row" id="printable-area">
                                    
                                    @for ($i = 0; $i < 10; $i++)
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Barcode</label>
                                            <p>
                                                <img src="{{ asset('storage/barcodes/' . $product_details->barcode . '.png') }}"
                                                    alt="Barcode">
                                            </p>
                                            <p>{{ $product_details->barcode }}</p>
                                        </div>
                                    </div>
                                    @endfor

                                </div>
                            </div> <!-- card-body -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Wrapper End -->
@endsection

@section('scripts')
    {{-- Include jQuery --}}

    {{-- Print Section Script --}}
    <script>
        window.printSection = function(divId) {
            var content = document.getElementById(divId).innerHTML;
            console.log(content);
            var myWindow = window.open('', '', 'height=600,width=1000');
            myWindow.document.write('');
            myWindow.document.write(content);
            myWindow.document.write('');
            myWindow.document.close();
            myWindow.focus();
            myWindow.print();
            myWindow.close();
        }
    </script>
@endsection