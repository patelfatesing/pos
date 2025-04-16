
@if(session('success'))
<div class="toast fade show bg-success text-white border-0 rounded p-2 mt-3" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header bg-success text-white">
       <svg class="bd-placeholder-img rounded mr-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img">
          <rect width="100%" height="100%" fill="#fff"></rect>
       </svg>
       <strong class="mr-auto text-white">LiquorHub</strong>
       <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
       <span aria-hidden="true">×</span>
       </button>
    </div>
    <div class="toast-body">
        {{ session('success') }}
    </div>
 </div>
   
@endif

@if(session('error'))
<div class="toast fade show bg-danger text-white border-0 rounded p-2 mt-3" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header bg-danger text-white">
       <svg class="bd-placeholder-img rounded mr-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img">
          <rect width="100%" height="100%" fill="#fff"></rect>
       </svg>
       <strong class="mr-auto text-white">LiquorHub</strong>
       <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
       <span aria-hidden="true">×</span>
       </button>
    </div>
    <div class="toast-body">
        {{ session('error') }}
    </div>
 </div>
   
@endif
