<?php

namespace App\Livewire;

use Livewire\Component;

class PaymentForm extends Component
{
    public $total = 0;
    public $cash = 0;
    public $upi = 0;
    public $updatingField = null;
    public $newtotalAmount = 0;

    public function mount($newtotalAmount)
    {
        $this->newtotalAmount = $newtotalAmount;
    }
    public function updatedCash($value)
    {
        if ($this->updatingField !== 'upi') {
            $this->updatingField = 'cash';
            $this->cash = floatval($value);
            $this->upi = $this->newtotalAmount - $this->cash;
            $this->updatingField = null;
        }
    }

    public function updatedUpi($value)
    {
        if ($this->updatingField !== 'cash') {
            $this->updatingField = 'upi';
            $this->upi = floatval($value);
            $this->cash = $this->newtotalAmount - $this->upi;
            $this->updatingField = null;
        }
    }

    public function render()
    {
        return view('livewire.payment-form');
    }
}
