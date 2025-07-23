{{-- Cashier Button --}}
@if (auth()->user()->hasRole('cashier'))
    <div class="sidebar-item" data-toggle="modal" data-target="#storeStockRequest" data-placement="top" title="{{ __('messages.store_stock_request') }}" style="cursor: pointer;">
        <button type="button" class="btn btn-default p-1 m-0 border-0 bg-transparent">
            <img src="{{ asset('public/external/frame2834471-mtm.svg') }}" alt="Stock Request Icon" />
        </button>
        <span>Stock Request</span>
    </div>
    <x-sidebar-separator />
@endif

{{-- Warehouse Button --}}
@if (auth()->user()->hasRole('warehouse'))
    <div class="sidebar-item" data-toggle="modal" data-target="#storeStockRequest" data-placement="top" title="{{ __('messages.store_stock_request') }}" style="cursor: pointer;">
        <button type="button" class="btn btn-default p-1 m-0 border-0 bg-transparent">
            <img src="{{ asset('public/external/frame2834471-mtm.svg') }}" alt="Stock Request Icon" style="width: 20px; height: 20px;" />
        </button>
        <span>Stock Request</span>
    </div>
    <x-sidebar-separator />
@endif

<div class="sidebar-item">
    <livewire:take-cash-modal wire:key="{{ ($isMobile ?? false) ? 'mobile-' : '' }}take-cash-modal-{{ auth()->id() }}" />
</div>
<x-sidebar-separator />

<div class="sidebar-item" data-toggle="modal" data-target="#cashout" data-placement="top" title="{{ __('messages.cash_out') }}" style="cursor: pointer;">
    <button type="button" class="btn btn-default p-1 m-0 border-0 bg-transparent">
        <img src="{{ asset('public/external/caseout.png') }}" alt="Cash Out Icon" width="32" height="32" />
    </button>
    <span>Cash Out</span>
</div>
<x-sidebar-separator />

@if (count($itemCarts) == 0)
    <div class="sidebar-item" data-toggle="modal" data-target="#holdTransactionsModal" data-placement="top" title="{{ __('messages.view_hold') }}" style="cursor: pointer;">
        <button type="button" class="btn btn-default p-1 m-0 border-0 bg-transparent">
            <img src="{{ asset('public/external/vector4471-4bnt.svg') }}" alt="View Hold Icon" style="width: 24px; height: 24px;" />
        </button>
        <span>View Hold</span>
    </div>
    <x-sidebar-separator />
@endif

<div class="sidebar-item">
    <livewire:order-modal wire:key="{{ ($isMobile ?? false) ? 'mobile-' : '' }}order-modal-{{ auth()->id() }}" />
</div>
<x-sidebar-separator />

@if (auth()->user()->hasRole('warehouse'))
    <div class="sidebar-item" wire:click="printLastInvoice" data-placement="top" title="{{ __('messages.print_the_last_invoice') }}" style="cursor: pointer;">
        <button type="button" class="btn btn-default p-1 m-0 border-0 bg-transparent">
            <img src="{{ asset('public/external/pdf_icon_final.jpg') }}" alt="Print Invoice Icon" style="width: 24px; height: 24px;" />
        </button>
        <span>Print Invoice</span>
    </div>
    <x-sidebar-separator />

    <div class="sidebar-item">
        <livewire:customer-credit-ledger-modal wire:key="{{ ($isMobile ?? false) ? 'mobile-' : '' }}credit-ledger-modal-{{ auth()->id() }}" />
    </div>
    <x-sidebar-separator />
@endif

@if (count($itemCarts) == 0 && auth()->user()->hasRole('warehouse'))
    <div class="sidebar-item">
        <livewire:collation-modal wire:key="{{ ($isMobile ?? false) ? 'mobile-' : '' }}collation-modal-{{ auth()->id() }}" />
    </div>
    <x-sidebar-separator />
@endif

<div class="sidebar-item">
    @livewire('shift-close-modal', [], key('{{ ($isMobile ?? false) ? 'mobile-' : '' }}shift-close-modal-' . auth()->id()))
</div>
