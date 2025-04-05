@extends('layouts.backend.layouts')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('page-content')
    <style>
        #camera video {
            width: 100%;
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
                                    <h4 class="card-title">Edit User - </h4>
                                </div>
                                <div>
                                    <a href="{{ route('users.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <video id="video" width="640" height="480" autoplay></video>
                                <canvas id="canvas" style="display: none;"></canvas>
                                <button id="snap">Capture Photo</button>

                                <form id="photoForm" enctype="multipart/form-data">
                                    <input type="hidden" name="photo" id="photo">
                                    <button type="submit">Upload</button>
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
        // Access camera
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                document.getElementById('video').srcObject = stream;
            });
    
        // Capture image
        document.getElementById('snap').addEventListener('click', () => {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
    
            const dataURL = canvas.toDataURL('image/png');
            document.getElementById('photo').value = dataURL;
        });
    
        // Upload to server
        document.getElementById('photoForm').addEventListener('submit', e => {
            e.preventDefault();
    
            const formData = new FormData();
            formData.append('photo', document.getElementById('photo').value);
    
            fetch('/pos/public/products/upload-pic', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => alert('Photo uploaded!'))
            .catch(err => console.error(err));
        });
    </script>
@endsection
