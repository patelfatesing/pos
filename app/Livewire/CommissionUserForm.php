<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Commissionuser;
use App\Models\CommissionUserImage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class CommissionUserForm extends Component
{
    use WithFileUploads;

    public $commissionUserId;
    public $user_id, $commission_type = 'fixed', $commission_value = 0.00, $applies_to = 'all', $reference_id;
    public $is_active = 1, $start_date, $end_date;
    public $images = [];
    public $existingImages = [];

    public function mount($id = null)
    {
        if ($id) {
            $this->commissionUserId = $id;
            $commission = Commissionuser::with('images')->where('status', 'Active')->findOrFail($id);
            $this->fill($commission->toArray());
            $this->existingImages = $commission->images;
        }
    }

    protected function rules()
    {
        return [
            'commission_type' => 'required|in:fixed,percentage',
            'commission_value' => 'required|numeric',
            'applies_to' => 'required|in:all,category,product',
            'reference_id' => 'nullable|integer',
            'is_active' => 'required|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'images.*' => 'nullable|image|max:2048',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = $this->only([
            'user_id', 'commission_type', 'commission_value', 'applies_to',
            'reference_id', 'is_active', 'start_date', 'end_date'
        ]);
        
        $commissionUser = $this->commissionUserId
            ? Commissionuser::where('status', 'Active')->findOrFail($this->commissionUserId)->update($data)
            : $commissionUser = Commissionuser::create($data);

        $id = $this->commissionUserId ?? $commissionUser->id;

        foreach ($this->images as $img) {
            $path = $img->store('commission_user_images', 'public');
            CommissionUserImage::create([
                'commission_user_id' => $id,
                'image_path' => $path,
                'image_name' => $img->getClientOriginalName(),
            ]);
        }

        session()->flash('success', $this->commissionUserId ? 'Updated successfully.' : 'Created successfully.');

        return redirect()->route('commission-users.index');
    }

    public function deleteImage($imageId)
    {
        $image = CommissionUserImage::findOrFail($imageId);
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
        $this->existingImages = $this->existingImages->filter(fn($img) => $img->id != $imageId);
        session()->flash('success', 'Image deleted.');
    }

    public function render()
    {
        return view('livewire.commission-user-form', [
            'users' => User::pluck('name', 'id'),
        ]);
    }
}
