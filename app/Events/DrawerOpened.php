<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DrawerOpened implements ShouldBroadcast
{
    public $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('drawer-channel');
    }

    public function broadcastAs(): string
    {
        return 'drawer.opened';
    }
}
