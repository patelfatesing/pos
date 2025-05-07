<?php
namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ButtonTimer extends Component
{
    public $endTime;
    public $buttonEnabled = false;

    public function mount($endTime)
    {
        Log::info('Mounting ButtonTimer component', ['endTime' => $endTime]);
        $this->endTime = Carbon::parse($endTime, 'Asia/Kolkata'); // Set timezone to IST
        $this->checkTime();
    }

    public function checkTime()
    {
        $now = Carbon::now('Asia/Kolkata'); // Use IST timezone
        $tenMinBeforeEnd = $this->endTime->copy()->subMinutes(10);

        Log::info('Checking time', [
            'now' => $now->toDateTimeString(),
            'tenMinBeforeEnd' => $tenMinBeforeEnd->toDateTimeString(),
            'endTime' => $this->endTime->toDateTimeString()
        ]);

        if ($now->greaterThanOrEqualTo($tenMinBeforeEnd)) {
            $this->buttonEnabled = true;
            Log::info('Button enabled');
        } else {
            $this->buttonEnabled = false;
            Log::info('Button disabled');
        }
    }

    public function render()
    {
        Log::info('Rendering ButtonTimer component');
        return view('livewire.button-timer');
    }
}
