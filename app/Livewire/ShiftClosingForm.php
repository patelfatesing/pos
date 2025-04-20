<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UserShift;
use App\Models\CashDetail;
use Illuminate\Support\Carbon;

class ShiftClosingForm extends Component
{
    public $shop_name = 'BR SHOP';
    public $start_time;
    public $end_time;
    public $opening_cash;
    public $deshi_sales;
    public $beer_sales;
    public $english_sales;
    public $discount;
    public $upi_payment;
    public $withdrawal_payment;
    public $cash = [];

    public function mount()
    {
        $this->start_time = now()->startOfDay();
        $this->end_time = now();
        $this->cash = [
            ['denomination' => 10, 'qty' => 0],
            ['denomination' => 20, 'qty' => 0],
            ['denomination' => 50, 'qty' => 0],
            ['denomination' => 100, 'qty' => 0],
            ['denomination' => 200, 'qty' => 0],
            ['denomination' => 500, 'qty' => 0],
        ];
    }

    public function save()
    {
        $total_sales = $this->deshi_sales + $this->beer_sales + $this->english_sales;
        $today_cash = $total_sales + $this->discount + $this->upi_payment + $this->withdrawal_payment;
        $closing_cash = array_sum(array_map(function ($item) {
            return $item['denomination'] * $item['qty'];
        }, $this->cash));
        $branch_id = auth()->user()->userinfo->branch->id ?? null;

        $startDate = Carbon::parse($this->start_time)->toDateString(); // YYYY-MM-DD
        $endDate   = Carbon::parse($this->end_time)->toDateString();   // YYYY-MM-DD

        $shift = UserShift::updateOrCreate(
            [
                'user_id'    => auth()->id(),
                'branch_id'  => $branch_id,
                'start_time' => $startDate,
                'end_time'   => $endDate,
            ],
            [
                'opening_cash'        => $this->opening_cash,
                'deshi_sales'         => $this->deshi_sales ?? 0,
                'beer_sales'          => $this->beer_sales ?? 0,
                'english_sales'       => $this->english_sales ?? 0,
                'discount'            => $this->discount ?? 0,
                'upi_payment'         => $this->upi_payment ?? 0,
                'withdrawal_payment'  => $this->withdrawal_payment ?? 0,
                'closing_cash'        => $closing_cash,
            ]
        );

        foreach ($this->cash as $item) {
            CashDetail::create([
                'shift_closing_id' => $shift->id,
                'denomination' => $item['denomination'],
                'quantity' => $item['qty'],
                'total' => $item['denomination'] * $item['qty'],
            ]);
        }

        session()->flash('message', 'Shift data saved successfully!');
        return redirect()->to('/shift-reports');
    }

    public function render()
    {
        return view('livewire.shift-closing-form');
    }
}
