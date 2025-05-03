<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LanguageSwitcher extends Component
{
    public string $language;

    public function mount(): void
    {
        $this->language = Session::get('locale', config('app.locale'));
    }
    public function changeLanguage()
    {
        Session::put('locale', $this->language);
        App::setLocale($this->language);

        redirect()->back()->send(); // Refresh to apply changes
    }


    public function render()
    {
        return view('livewire.language-switcher');
    }
}
