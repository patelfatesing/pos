<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductUserUpload extends Component
{
    use WithFileUploads;

    public $step = 1;

    public $productImage;
    public $userImageData;

    public function nextStep()
    {
        $this->validate([
            'productImage' => 'required|image|max:2048',
        ]);

        $this->step = 2;
    }

    public function submit()
    {
        $this->validate([
            'userImageData' => 'required|string',
        ]);

        // Save product image
        $productPath = $this->productImage->store('products', 'public');

        // Save user image (from base64)
        $image = str_replace('data:image/png;base64,', '', $this->userImageData);
        $image = str_replace(' ', '+', $image);
        $filename = 'users/' . Str::random(10) . '.png';
        Storage::disk('public')->put($filename, base64_decode($image));

        session()->flash('success', 'Both images uploaded successfully!');

        // Reset state
        $this->reset(['step', 'productImage', 'userImageData']);
        $this->step = 1;
    }

    public function render()
    {
        return view('livewire.product-user-upload');
    }
}
