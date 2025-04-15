<?php

namespace App\Livewire;

use Livewire\Component;

class CashBreakdown extends Component
{
    public $cashAmount = 3000;
    public $noteDenominations = [
        500 => 0,
        2000 => 0,
        200 => 0,
        100 => 0,
    ];
    public $remainingAmount;
    public $totalBreakdown = [];

    public function mount()
    {
        $this->remainingAmount = $this->cashAmount;
    }

    public function calculateBreakdown()
    {
        $remaining = $this->cashAmount;
        $this->totalBreakdown = [];

        foreach ($this->noteDenominations as $note => $count) {
            $breakdown = $note * $count;
            $remaining -= $breakdown;

            $this->totalBreakdown[$note] = $breakdown;
        }

        $this->remainingAmount = $remaining;
    }

    public function render()
    {
        return view('livewire.cash-breakdown');
    }
}
