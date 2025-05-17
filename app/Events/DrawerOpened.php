<?php

// app/Events/DrawerOpened.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DrawerOpened implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $notify_to;
    public $message;
    public $customer;
    public $type;
    public $value;
    public $nfid;

    public function __construct($data)
    {
        $this->message = $data['message'];
        $this->customer = $data['customer'];
        $this->type = $data['type'];
        $this->value = $data['value'];
        $this->nfid = $data['nfid'];
        $this->notify_to = $data['notify_to'];
    }

    public function broadcastOn()
    {
        return new Channel('drawer-channel');
    }

    public function broadcastAs()
    {
        return 'DrawerOpened';
    }
}
