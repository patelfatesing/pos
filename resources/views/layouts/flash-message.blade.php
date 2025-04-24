@if(session('success'))
<div 
    x-data="{ show: true }" 
    x-show="show"
    @click.outside="show = false"
    x-transition
    class="toast fade show bg-success text-white border-0 rounded p-2 mt-3"
    role="alert" aria-live="assertive" aria-atomic="true"
>
    <div class="toast-header bg-success text-white">
        {{-- Success Icon (Checkmark) --}}
        <svg class="rounded mr-2" width="20" height="20" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/>
        </svg>
        <strong class="mr-auto text-white">LiquorHub</strong>
        <button @click="show = false" type="button" class="ml-2 mb-1 close text-white" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>
    <div class="toast-body">
        {{ session('success') }}
    </div>
</div>
@endif

@if(session('error'))
<div 
    x-data="{ show: true }" 
    x-show="show"
    @click.outside="show = false"
    x-transition
    class="toast fade show bg-danger text-white border-0 rounded p-2 mt-3"
    role="alert" aria-live="assertive" aria-atomic="true"
>
    <div class="toast-header bg-danger text-white">
        {{-- Error Icon (Exclamation / Cross) --}}
        <svg class="rounded mr-2" width="20" height="20" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
        </svg>
        <strong class="mr-auto text-white">LiquorHub</strong>
        <button @click="show = false" type="button" class="ml-2 mb-1 close text-white" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>
    <div class="toast-body">
        {{ session('error') }}
    </div>
</div>
@endif
