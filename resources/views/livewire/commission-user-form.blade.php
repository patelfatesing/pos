<div>
    <form wire:submit.prevent="save" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>User</label>
            <select wire:model="user_id" class="form-control">
                <option value="">-- Select --</option>
                @foreach($users as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            @error('user_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Commission Type</label>
                <select wire:model="commission_type" class="form-control">
                    <option value="fixed">Fixed</option>
                    <option value="percentage">Percentage</option>
                </select>
            </div>

            <div class="form-group col-md-6">
                <label>Commission Value</label>
                <input type="number" wire:model="commission_value" step="0.01" class="form-control" />
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Applies To</label>
                <select wire:model="applies_to" class="form-control">
                    <option value="all">All</option>
                    <option value="category">Category</option>
                    <option value="product">Product</option>
                </select>
            </div>

            <div class="form-group col-md-6">
                <label>Reference ID</label>
                <input type="number" wire:model="reference_id" class="form-control" />
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Start Date</label>
                <input type="date" wire:model="start_date" class="form-control" />
            </div>
            <div class="form-group col-md-6">
                <label>End Date</label>
                <input type="date" wire:model="end_date" class="form-control" />
            </div>
        </div>

        <div class="form-group">
            <label>Active</label>
            <select wire:model="is_active" class="form-control">
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>

        <div class="form-group">
            <label>Upload Images</label>
            <input type="file" wire:model="images" multiple class="form-control" />
            @error('images.*') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        @if ($existingImages)
            <div class="row">
                @foreach ($existingImages as $img)
                    <div class="col-md-3">
                        <div class="card mb-2">
                            <img src="{{ asset('storage/' . $img->image_path) }}" class="card-img-top" alt="Image">
                            <div class="card-body p-2 text-center">
                                <button type="button" wire:click="deleteImage({{ $img->id }})" class="btn btn-danger btn-sm">Delete</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <button class="btn btn-primary">Save</button>
    </form>
</div>
