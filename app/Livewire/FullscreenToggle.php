<?php

namespace App\Livewire;

use Livewire\Component;

class FullscreenToggle extends Component
{
    public $isFullscreen = false;

    public function toggleFullscreen()
    {
        $this->isFullscreen = !$this->isFullscreen;

        // Trigger frontend JS
        $this->dispatch('toggleFullscreen');
    }

    public function render()
    {
        return view('livewire.fullscreen-toggle');
    }
}
