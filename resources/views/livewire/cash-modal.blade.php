<!-- Modal -->
<div wire:ignore.self class="modal fade" id="cashModal" tabindex="-1" aria-labelledby="cashModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content p-3">
        <div class="modal-header">
          <h5 class="modal-title">Cash Payment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <div class="mb-3">
            <label>Cash Amount:</label>
            <input type="text" class="form-control"  value="{{ $cash }}" disabled>
          </div>
          <div class="mb-3">
            <label>Tendered:</label>
            <input type="text" class="form-control" wire:model="tendered" readonly>
          </div>
          <div class="mb-3">
            <label>Change:</label>
            <input type="text" class="form-control" value="{{ $change }}" disabled>
          </div>
  
          <!-- Number Pad -->
          <div class="row text-center">
            @foreach ([1,2,3,4,5,6,7,8,9,0] as $num)
              <div class="col-4 p-2">
                <button class="btn btn-primary w-100" wire:click="addAmount('{{ $num }}')">{{ $num }}</button>
              </div>
            @endforeach
            <div class="col-6 p-2">
              <button class="btn btn-warning w-100" wire:click="clearTendered()">Clear</button>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" data-bs-dismiss="modal">Pay</button>
        </div>
      </div>
    </div>
  </div>
  