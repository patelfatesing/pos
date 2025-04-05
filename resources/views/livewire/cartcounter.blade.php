<div>
    <a href="{{ route('items.cart') }}" class="search-toggle dropdown-toggle btn border add-btn {{ request()->routeIs('items.cart') ? 'active' : '' }}">
        <i class="fas fa-shopping-cart"></i> <!-- Cart symbol -->
        <span class="badge bg-danger">{{ $total }}</span> <!-- Red badge for total -->
    </a>
</div>
