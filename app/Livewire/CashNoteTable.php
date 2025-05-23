<?php

namespace App\Livewire;

use Livewire\Component;

class CashNoteTable extends Component
{
    public $noteDenominations = [10, 20, 50, 100, 200,500];
    public $cashNotes = [];
    public $cashAmount = 0;

    public $selectedSalesReturn = null;

    public $cashPaTenderyAmt = 0;
    public $cashPayChangeAmt = 0;

    public function mount()
    {
        foreach ($this->noteDenominations as $index => $denomination) {
            $this->cashNotes[$index][$denomination] = ['in' => 0, 'out' => 0];
        }
    }

    public function incrementNote($key, $denomination, $type)
    {
        $this->cashNotes[$key][$denomination][$type]++;
    }

    public function decrementNote($key, $denomination, $type)
    {
        if ($this->cashNotes[$key][$denomination][$type] > 0) {
            $this->cashNotes[$key][$denomination][$type]--;
        }
    }

    public function clearCashNotes()
    {
        foreach ($this->noteDenominations as $index => $denomination) {
            $this->cashNotes[$index][$denomination] = ['in' => 0, 'out' => 0];
        }
    }

    public function getTotals()
    {
        $totalIn = $totalOut = $totalAmount = 0;

        foreach ($this->noteDenominations as $key => $denomination) {
            $in = $this->cashNotes[$key][$denomination]['in'] ?? 0;
            $out = $this->cashNotes[$key][$denomination]['out'] ?? 0;

            $totalIn += $in * $denomination;
            $totalOut += $out * $denomination;
            $totalAmount += ($in - $out) * $denomination;
        }

        $this->cashPaTenderyAmt = $totalIn;
        $this->cashPayChangeAmt = $this->cashAmount - $totalIn;

        return compact('totalIn', 'totalOut', 'totalAmount');
    }

    public function render()
    {
        $totals = $this->getTotals();

        return view('livewire.cash-note-table', [
            'totals' => $totals,
        ]);
    }
}
