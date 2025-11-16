<?php
return [

    // Actions that can exist in the system (we’ll subset per module)
    'binary' => ['enable', 'create'],                 // yes/no
    'scoped' => ['listing', 'view', 'update', 'delete'], // none|own|all

    // Per-module capability matrix
    // - actions: which actions apply at MODULE level (subset of binary+scoped)
    // - submodules: an array of submodules; each can have its own actions subset
    'modules' => [
        'inventory' => [
            'label'   => 'Inventory',
            'actions' => ['enable', 'view', 'listing'], // only these
            'submodules' => [
                'inventory' => [
                    'label'   => 'inventory',
                    'actions' => ['enable', 'create', 'listing', 'view', 'update', 'delete'], // full set on submodule
                ],
                'stock_request' => [
                    'label'   => 'Stock Request',
                    'actions' => ['enable', 'create', 'listing', 'view', 'update', 'delete'], // full set on submodule
                ],
                'stock_transfer' => [
                    'label'   => 'Stock Transfer',
                    'actions' => ['enable', 'create', 'listing', 'view', 'update', 'delete'], // full set on submodule
                ],
            ], // none
        ],

        'store' => [
            'label'   => 'Store',
            'actions' => ['enable', 'listing', 'view'], // no create/update/delete at module level
            'submodules' => [], // none
        ],

        'users' => [
            'label'   => 'Users',
            'actions' => ['enable', 'view', 'listing'], // only these
            'submodules' => [], // none
        ],

        'accounting' => [
            'label'   => 'Accounting',
            'actions' => ['enable'], // only enable at module level
            'submodules' => [
                // submodule key => label + actions
                'vouchers' => [
                    'label'   => 'Vouchers',
                    'actions' => ['enable', 'create', 'listing', 'view', 'update', 'delete'], // full set on submodule
                ],
                // 'invoices' => ['label'=>'Invoices','actions'=>['enable','listing','view']],
            ],
        ],

        // Example: a module with *only* enable and NO submodules
        'settings' => [
            'label'   => 'Settings',
            'actions' => ['enable'], // just a switch
            'submodules' => [],
        ],
    ],
];
