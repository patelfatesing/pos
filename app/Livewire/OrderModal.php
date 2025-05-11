<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Invoice;
use Carbon\Carbon;
use App\Models\UserShift;

class OrderModal extends Component
{
    public $orders = [];
    public $showModal = false;

    public function openModal()
    {
        $today = Carbon::today();
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";


        $currentShift = UserShift::whereDate('start_time', $today)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where(['status' =>"pending"])->first();

        $start_date = @$currentShift->start_time; // your start date (set manually)
        $end_date = date('Y-m-d') . ' 23:59:59'; // today's date till end of day
        $this->orders = Invoice::where('user_id', auth()->user()->id)
            ->where('branch_id', $branch_id)
            ->whereIn('status', ['Refunded', 'Paid']) // <-- added condition
            ->whereBetween('created_at', [$start_date, $end_date])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        $this->showModal = true;

        // Dispatch browser event to show modal
        $this->dispatch('show-order-modal');
    }

    public function render()
    {
        return view('livewire.order-modal');
    }
}

