<div wire:ignore.self class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
  
        <div class="modal-header">
          <h5 class="modal-title">Upload Product & User Images</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
  
        <div class="modal-body">
          @if (session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
  
          @if ($step === 1)
            <div>
              <label>Upload Product Image</label>
              <input type="file" wire:model="productImage" class="form-control">
              @error('productImage') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
          @elseif ($step === 2)
            <div>
              <label>Take User Picture</label>
              <div class="text-center">
                <video id="video" width="100%" autoplay class="mb-2 rounded shadow"></video>
                <canvas id="canvas" class="d-none"></canvas>
                <br>
                <button type="button" class="btn btn-secondary" onclick="takePicture()">Capture</button>
              </div>
              <input type="hidden" wire:model="userImageData">
              <div id="preview" class="mt-3"></div>
              @error('userImageData') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
          @endif
        </div>
  
        <div class="modal-footer">
          @if ($step === 1)
            <button type="button" class="btn btn-primary" wire:click="nextStep">Next</button>
          @elseif ($step === 2)
            <button type="button" class="btn btn-success" wire:click="submit">Submit</button>
          @endif
        </div>
  
      </div>
    </div>
  </div>
  
  @push('scripts')
  <script>
  document.addEventListener('livewire:load', () => {
      const video = document.getElementById('video');
      const canvas = document.getElementById('canvas');
      const preview = document.getElementById('preview');
  
      // Start camera
      navigator.mediaDevices.getUserMedia({ video: true })
          .then(stream => {
              video.srcObject = stream;
          })
          .catch(err => {
              console.error("Camera access denied: ", err);
          });
  
      window.takePicture = () => {
          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
          const imageData = canvas.toDataURL('image/png');
          Livewire.find('@this.__instance.id').set('userImageData', imageData);
  
          preview.innerHTML = `<img src="${imageData}" class="img-fluid rounded mt-2" />`;
      };
  });
  </script>
  @endpush
  