<?php
namespace App\Livewire;

use Livewire\Component;

class CartPayment extends Component
{
    public $cashAmount = 0;
    public $tenderedAmount = 0;
    public $changeAmount = 0;

    public $noteDenominations = [
        2000 => 0,
        500 => 0,
        200 => 0,
        100 => 0,
    ];

    public $totalBreakdown = [];
    public $remainingAmount = 0;

    public $commissionAmount = 0;
    public $partyAmount = 0;

    public function updatedNoteDenominations()
    {
        $this->calculateBreakdown();
    }

    public function updatedTenderedAmount()
    {
        $this->changeAmount = $this->tenderedAmount - $this->cashAmount;
    }

    public function calculateBreakdown()
    {
        $this->totalBreakdown = [];
        $total = 0;

        foreach ($this->noteDenominations as $note => $count) {
            $amount = $note * $count;
            $this->totalBreakdown[$note] = $amount;
            $total += $amount;
        }

        $this->remainingAmount = $this->cashAmount - $total;
    }

    public function render()
    {
        return view('livewire.cash-payment');
    }
}
