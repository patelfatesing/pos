<div>
    <a href="{{ route('items.cart') }}" class="search-toggle dropdown-toggle btn border add-btn {{ request()->routeIs('items.cart') ? 'active' : '' }}">
        <i class="fas fa-fax ms-2"></i> <!-- Cart symbol -->
        POS
       <!--  <span class="badge bg-danger">{{ $total }}</span> Red badge for total -->
    </a>
</div>
