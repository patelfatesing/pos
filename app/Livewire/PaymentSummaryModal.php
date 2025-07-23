<?php

namespace App\Livewire;

use Livewire\Component;

class PaymentSummaryModal extends Component
{
    public $showModal = false;
    public $headertitle = "";

    public function openModal()
    {
        $this->showModal = true;
    }

    public function render()
    {
        return view('livewire.payment-summary-modal');
    }
}
