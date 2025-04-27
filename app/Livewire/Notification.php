<?php

namespace App\Livewire;

use Livewire\Component;

class Notification extends Component
{
    public $notifications = [];

    public $showPopup = false;

    public $selectedNotification = null;

    public function togglePopup()
    {
        $this->showPopup = !$this->showPopup;
    }
    public function viewNotificationDetail($notificationId)
    {
        $this->selectedNotification = $this->notifications[$notificationId];
    }
    
    public function closeNotificationDetail()
    {
        $this->selectedNotification = null;
    }
    
    public function render()
    {
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
    
        // Fetch notifications based on the user's ID and branch ID
        $getNotification = getNotificationsByNotifyTo(auth()->id(), $branch_id, 5);
        $notiAry=[];
        foreach ($getNotification as $key => $noti) {
            $notiAry[$key]['message']=$noti->content;
            $notiAry[$key]['time']=$noti->created_at->diffForHumans();
        }
        $this->notifications=$notiAry;
    
        // Debugging: Check the formatted notifications
    
        return view('livewire.notification');
    }
    
}
