<?php

namespace App\Livewire;

use Livewire\Component;


class CashModal extends Component
{
    public $cash = 0;
    public $tendered = 0;
    public $change = 0;

    protected $listeners = ['openCashModal' => 'resetFieldsAndOpen'];

    public function mount()
    {
        $this->cash = 0;
        $this->tendered = 0;
        $this->change = 0;
    }
    public function updatedTendered()
    {
        $this->calculateChange();
    }

    public function calculateChange()
    {
        $this->change = max(0, $this->tendered - $this->cash);
    }

    public function addAmount($value)
    {
        $this->tendered .= $value;
        $this->tendered = ltrim($this->tendered, '0'); // Remove leading zeros
        $this->calculateChange();
    }

    public function clearTendered()
    {
        $this->tendered = 0;
        $this->calculateChange();
    }

    public function resetFieldsAndOpen()
    {
        $this->cash = 100; // Set actual bill amount here
        $this->tendered = 0;
        $this->change = 0;

        $this->dispatch('show-cash-modal');
    }

    public function render()
    {
        return view('livewire.cash-modal');
    }
}