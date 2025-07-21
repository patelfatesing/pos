<div class="sidebar-item">
    {{-- Cashier Button --}}
    @if (auth()->user()->hasRole('cashier'))
        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#storeStockRequest"
            data-toggle="tooltip" data-placement="top" title="{{ __('messages.store_stock_request') }}">
            <img src="{{ asset('public/external/frame2834471-mtm.svg') }}" alt="Stock Request Icon" />
        </button>
        <span>Stock Request</span>
    @endif

    {{-- Warehouse Button --}}
    @if (auth()->user()->hasRole('warehouse'))
        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#warehouseStockRequest"
            data-toggle="tooltip" data-placement="top" title="{{ __('messages.warehouse_stock_request') }}">
            <img src="{{ asset('public/external/frame2834471-mtm.svg') }}" alt="Stock Request Icon" />
        </button>
        <span>Stock Request</span>
    @endif
</div>
<img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}" alt="Separator"
    class="main-screen-rectangle457" />

<div class="sidebar-item">
    <livewire:take-cash-modal />
    <span>Add Cash</span>
</div>
<img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}" alt="Separator"
    class="main-screen-rectangle457" />

<div class="sidebar-item">
    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#cashout" data-toggle="tooltip"
        data-placement="top" title="{{ __('messages.cash_out') }}">
        <img src="{{ asset('public/external/caseout.png') }}" alt="Cash Out Icon" width="32" height="32" />
    </button>
    <span>Cash Out</span>
</div>
<img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}" alt="Separator"
    class="main-screen-rectangle457" />

@if (count($itemCarts) == 0)
    <div class="sidebar-item">
        <button type="button" class="btn btn-default ml-2" data-toggle="modal" data-target="#holdTransactionsModal"
            data-toggle="tooltip" data-placement="top" title="{{ __('messages.view_hold') }}">
            <img src="{{ asset('public/external/vector4471-4bnt.svg') }}" alt="View Hold Icon" />
        </button>
        <span>View Hold</span>
    </div>
    <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}" alt="Separator"
        class="main-screen-rectangle457" />
@endif

<div class="sidebar-item">
    <livewire:order-modal />
    <span>Sales History</span>

</div>
<img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}" alt="Separator"
    class="main-screen-rectangle457" />

@if (auth()->user()->hasRole('warehouse'))
    <div class="sidebar-item">
        <button wire:click="printLastInvoice" class="btn btn-default ml-2" data-toggle="tooltip" data-placement="top"
            title="{{ __('messages.print_the_last_invoice') }}">
            <img src="{{ asset('public/external/pdf_icon_final.jpg') }}" alt="Print Invoice Icon" />
        </button>
        <span>Print Invoice</span>
    </div>
    <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}" alt="Separator"
        class="main-screen-rectangle457" />
@endif

@if (auth()->user()->hasRole('warehouse'))
    <div class="sidebar-item">
        <livewire:customer-credit-ledger-modal />
        <span>Customer Credit Ledger</span>
    </div>
    <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}" alt="Separator"
        class="main-screen-rectangle457" />
@endif

@if (count($itemCarts) == 0 && auth()->user()->hasRole('warehouse'))
    <div class="sidebar-item">
        <livewire:collation-modal />
        <span>Collect Credit</span>
    </div>
    <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}" alt="Separator"
        class="main-screen-rectangle457" />
@endif

<div class="sidebar-item">
    @livewire('shift-close-modal')
    <span>Close Shift</span>
</div>
