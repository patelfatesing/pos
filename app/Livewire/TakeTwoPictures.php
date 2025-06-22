<?php
namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Auth;
use App\Models\PartyUserImage;
use App\Models\CommissionUserImage;

class TakeTwoPictures extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $productPhoto = null;
    public ?TemporaryUploadedFile $customerPhoto = null;
    public bool $isProductPhotoTaken = false;
    public bool $isCustomerPhotoTaken = false;
    public bool $showHoldImg = false;
    public $partyHoldPic="";
    public $commissionHoldPic="";
    // Session keys for storing photo paths
    private const SESSION_KEY_PRODUCT = 'product_photo_path';
    private const SESSION_KEY_CUSTOMER = 'customer_photo_path';
    private const SESSION_KEY_TIMESTAMP = 'photos_timestamp';
    protected $listeners = ['resetPicAll','setImg','setHoldImage','resetHoldPic'];

    /**
     * Validation rules for the photos
     */
    protected array $rules = [
        'productPhoto' => 'required|image|max:2048|mimes:jpg,jpeg,png',
        'customerPhoto' => 'required|image|max:2048|mimes:jpg,jpeg,png',
    ];

    /**
     * Custom validation messages
     */
    protected array $messages = [
        'productPhoto.required' => 'Product photo is required.',
        'productPhoto.image' => 'Product photo must be an image.',
        'productPhoto.max' => 'Product photo must not exceed 2MB.',
        'productPhoto.mimes' => 'Product photo must be a JPG, JPEG or PNG file.',
        'customerPhoto.required' => 'Customer photo is required.',
        'customerPhoto.image' => 'Customer photo must be an image.',
        'customerPhoto.max' => 'Customer photo must not exceed 2MB.',
        'customerPhoto.mimes' => 'Customer photo must be a JPG, JPEG or PNG file.',
    ];

    /**
     * Lifecycle hook - runs when product photo is updated
     */
    public function updatedProductPhoto($value): void
    {
        if ($value) {
            $this->validateOnly('productPhoto');
            $this->isProductPhotoTaken = true;
            $this->dispatch('photo-status-updated', [
                'type' => 'product',
                'taken' => true
            ]);
        }
    }

    /**
     * Lifecycle hook - runs when customer photo is updated
     */
    public function updatedCustomerPhoto($value): void
    {
        if ($value) {
            $this->validateOnly('customerPhoto');
            $this->isCustomerPhotoTaken = true;
            $this->dispatch('photo-status-updated', [
                'type' => 'customer',
                'taken' => true
            ]);
        }
    }

    /**
     * Save both photos and store their paths
     */
    public function save(): void
    {
        try {
            $this->validate();

            if (!$this->isProductPhotoTaken || !$this->isCustomerPhotoTaken) {
                session()->flash('error', 'Both product and customer photos are required.');
                return;
            }

            // Generate unique filenames with timestamps
            $timestamp = now()->format('Y-m-d_H-i-s');
            $productFilename = "product_{$timestamp}.{$this->productPhoto->extension()}";
            $customerFilename = "customer_{$timestamp}.{$this->customerPhoto->extension()}";

            // Store the files with unique names
            $productPath = $this->productPhoto->storeAs(
                'photos/products', 
                $productFilename, 
                'public'
            );
            
            $customerPath = $this->customerPhoto->storeAs(
                'photos/customers', 
                $customerFilename, 
                'public'
            );

            if (!$productPath || !$customerPath) {
                throw new \Exception('Failed to save one or both photos.');
            }

            // Store paths in session
            $this->storePhotoPathsInSession($productPath, $customerPath);

            
            // Reset the form and states
            $this->resetAll();
            
            $this->dispatch('photos-saved', [
                'product' => $productPath,
                'customer' => $customerPath
            ]);

        } catch (\Throwable $e) {
            
            // Clean up any partially uploaded files
            if (isset($productPath) && Storage::disk('public')->exists($productPath)) {
                Storage::disk('public')->delete($productPath);
            }
            if (isset($customerPath) && Storage::disk('public')->exists($customerPath)) {
                Storage::disk('public')->delete($customerPath);
            }
            
            report($e); // Log the error
        }
    }

    /**
     * Store photo paths in session
     */
    private function storePhotoPathsInSession(string $productPath, string $customerPath): void
    {
        session([
            auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_PRODUCT => $productPath,
            auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_CUSTOMER => $customerPath,
            auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_TIMESTAMP => now()->timestamp
        ]);
    }

    /**
     * Get stored photo paths from session
     */
    public function getStoredPhotoPaths(): array
    {
        return [
            'product' => session(auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_PRODUCT),
            'customer' => session(auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_CUSTOMER),
            'timestamp' => session(auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_TIMESTAMP)
        ];
    }

    /**
     * Clear stored photo paths from session
     */
    public function clearStoredPhotoPaths(): void
    {
        session()->forget([
            auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_PRODUCT,
            auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_CUSTOMER,
            self::SESSION_KEY_TIMESTAMP
        ]);
    }

    /**
     * Check if photos are stored in session
     */
    public function hasStoredPhotos(): bool
    {
        return session()->has(auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_PRODUCT) && 
               session()->has(auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_CUSTOMER);
    }

    /**
     * Reset a specific photo
     */
    public function resetPhoto(string $type): void
    {
        if ($type === 'product') {
            $this->reset('productPhoto');
            $this->isProductPhotoTaken = false;
            $this->resetValidation('productPhoto');
            $this->dispatch('photo-status-updated', [
                'type' => 'product',
                'taken' => false
            ]);
        } elseif ($type === 'customer') {
            $this->reset('customerPhoto');
            $this->isCustomerPhotoTaken = false;
            $this->resetValidation('customerPhoto');
            $this->dispatch('photo-status-updated', [
                'type' => 'customer',
                'taken' => false
            ]);
        }
    }

    /**
     * Reset both photos and states
     */
    public function resetAll(): void
    {
        $this->reset(['productPhoto', 'customerPhoto']);
        $this->isProductPhotoTaken = false;
        $this->isCustomerPhotoTaken = false;
        $this->resetValidation();
        $this->dispatch('photos-reset');
    }
    public function resetHoldPic(): void
    {
        $this->reset(['partyHoldPic', 'commissionHoldPic','showHoldImg']);

    }
     public function resetPicAll(): void
    {
        $this->reset(['productPhoto', 'customerPhoto']);
        $this->isProductPhotoTaken = false;
        $this->isCustomerPhotoTaken = false;
        $this->resetValidation();
        // Remove stored photo paths from session
        session()->forget([
            auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_PRODUCT,
            auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_CUSTOMER,
            auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_TIMESTAMP,
        ]);
        $this->dispatch('photos-reset');
    }

    /**
     * Check if both photos are taken
     */
    public function areBothPhotosTaken(): bool
    {
        return $this->isProductPhotoTaken && $this->isCustomerPhotoTaken;
    }

    /**
     * Get public URL for a stored photo
     */
    public function getPhotoUrl(string $type): ?string
    {
        $path = session(
            $type === 'product' ? auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_PRODUCT : auth()->id()."_".Auth::user()->role->name."_".self::SESSION_KEY_CUSTOMER
        );

        return $path ? $path: null;
    }
   public function getHoldPhotoUrl(string $type): ?array
    {
        $userKeyPrefix = auth()->id() . "_" . Auth::user()->role->name;

        if ($type === 'party') {
            $customerPath = session($userKeyPrefix . '_party_custtomer_img');
            $productPath = session($userKeyPrefix . '_party_product_img');

            return [
                $type.'_customer' => $customerPath ?: null,
                $type.'_product' => $productPath ?: null,
            ];
        }else if ($type === 'commission') {
            $customerPath = session($userKeyPrefix . '_commission_custtomer_img');
            $productPath = session($userKeyPrefix . '_commission_product_img');

            return [
                $type.'_customer'  => $customerPath ?: null,
                $type.'_product'=> $productPath ?: null,
            ];
        } 

        return null;
    }
    public function setImg()
    {
        if(!empty($this->partyHoldPic)){
             $this->showHoldImg = true;
        }else{

            $this->showHoldImg = false;
        }
        $current_party_id = session('current_party_id');
        if(!empty($current_party_id)){
            $partyUserImage = PartyUserImage::where('party_user_id', $current_party_id)->where('type', 'hold')->first(["image_path","product_image_path"]);
            if(!empty($partyUserImage)){
             
                $this->setHoldImage( [
                'type' => "party",
                'customer' => $partyUserImage->image_path,
                'product' => $partyUserImage->product_image_path
                ]);
                $this->showHoldImg = true;

            }
        }
        if(!empty($current_party_id)){
             $commissionUserImage = CommissionUserImage::where('commission_user_id', $current_party_id)->where('type', 'hold')->first("image_path","product_image_path");
            if(!empty($commissionUserImage)){
                $this->setHoldImage( [
                'type' => "party",
                'customer' => $commissionUserImage->image_path,
                'product' => $commissionUserImage->product_image_path
                ]);
                $this->showHoldImg = true;
            }
        }
       // $this->partyHoldPic=$this->getHoldPhotoUrl('party');
        //$this->commissionHoldPic=$this->getHoldPhotoUrl('commission');
    }
    public function setHoldImage($imageData){
        
        $type=$imageData['type'] ??'';
        if($imageData['type']=="party")
        {
            $this->partyHoldPic=[
                $type.'_customer' => $imageData['customer'] ?: null,
                $type.'_product' => $imageData['product'] ?: null,
            ];

        }
        else{
            $this->partyHoldPic=[
                $type.'_customer' => $imageData['customer'] ?: null,
                $type.'_product' => $imageData['product'] ?: null,
            ];
        }

    }
    /**
     * Render the component
     */
    public function render(): View
    {
        return view('livewire.take-two-pictures', [
            'canSave' => $this->areBothPhotosTaken(),
            'storedPhotos' => $this->hasStoredPhotos() ? $this->getStoredPhotoPaths() : null,
            'productPhotoUrl' => $this->getPhotoUrl('product'),
            'customerPhotoUrl' => $this->getPhotoUrl('customer'),
        ]);
    }
}
