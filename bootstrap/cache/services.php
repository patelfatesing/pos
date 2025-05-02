<?php return array (
  'providers' => 
  array (
    0 => 'Barryvdh\\DomPDF\\ServiceProvider',
    1 => 'Laravel\\Breeze\\BreezeServiceProvider',
    2 => 'Laravel\\Pail\\PailServiceProvider',
    3 => 'Laravel\\Sail\\SailServiceProvider',
    4 => 'Laravel\\Tinker\\TinkerServiceProvider',
    5 => 'Livewire\\LivewireServiceProvider',
    6 => 'Carbon\\Laravel\\ServiceProvider',
    7 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    8 => 'Termwind\\Laravel\\TermwindServiceProvider',
    9 => 'Maatwebsite\\Excel\\ExcelServiceProvider',
    10 => 'App\\Providers\\AppServiceProvider',
  ),
  'eager' => 
  array (
    0 => 'Barryvdh\\DomPDF\\ServiceProvider',
    1 => 'Laravel\\Pail\\PailServiceProvider',
    2 => 'Livewire\\LivewireServiceProvider',
    3 => 'Carbon\\Laravel\\ServiceProvider',
    4 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    5 => 'Termwind\\Laravel\\TermwindServiceProvider',
    6 => 'Maatwebsite\\Excel\\ExcelServiceProvider',
    7 => 'App\\Providers\\AppServiceProvider',
  ),
  'deferred' => 
  array (
    'Laravel\\Breeze\\Console\\InstallCommand' => 'Laravel\\Breeze\\BreezeServiceProvider',
    'Laravel\\Sail\\Console\\InstallCommand' => 'Laravel\\Sail\\SailServiceProvider',
    'Laravel\\Sail\\Console\\PublishCommand' => 'Laravel\\Sail\\SailServiceProvider',
    'command.tinker' => 'Laravel\\Tinker\\TinkerServiceProvider',
  ),
  'when' => 
  array (
    'Laravel\\Breeze\\BreezeServiceProvider' => 
    array (
    ),
    'Laravel\\Sail\\SailServiceProvider' => 
    array (
    ),
    'Laravel\\Tinker\\TinkerServiceProvider' => 
    array (
    ),
  ),
);