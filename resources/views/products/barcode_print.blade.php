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
    @push('scripts')
<script>
    window.addEventListener('print-section', event => {
        const content = document.getElementById('printable-section').innerHTML;

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = content;
        tempDiv.querySelectorAll('script').forEach(el => el.remove());

        const cleanContent = tempDiv.innerHTML;

        const myWindow = window.open('', '', 'height=600,width=1000');
        myWindow.document.write('<html><head><title>Print</title>');
        myWindow.document.write('<style>');
        myWindow.document.write(`
            body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
            .form-group { text-align: center; margin-bottom: 20px; }
            img { max-width: 100%; height: auto; }
            .row { display: flex; flex-wrap: wrap; justify-content: center; }
            .col-md-4 { width: 30%; margin-bottom: 20px; }
        `);
        myWindow.document.write('</style></head><body>');
        myWindow.document.write(cleanContent);
        myWindow.document.write('</body></html>');
        myWindow.document.close();
        myWindow.focus();
        setTimeout(() => {
            myWindow.print();
            myWindow.close();
        }, 500);
    });
</script>
@endpush

@endsection
