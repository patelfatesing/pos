<div>
    <!-- Notification Icon -->
    <button wire:click="togglePopup" class="btn btn-primary mr-1 position-relative">
        <i class="fas fa-bell"></i>
        @if ($readNotificationsCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $readNotificationsCount }}
            </span>
        @endif
    </button>

    <!-- Notification Popup -->
    @if ($showPopup)
        <div class="dropdown-menu dropdown-menu-end p-0 show iq-sub-dropdown" style="width: 360px;"
            aria-labelledby="dropdownMenuButton" id="notificationDropdown">
            <div class="card shadow-sm border-0 m-0">
                <div
                    class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3">
                    <h6 class="mb-0">Notifications    <span class="badge bg-light text-primary"
                        id="all_notificationCount">{{ count($notifications) }}</span></h6>
                    
                         <!-- Close button -->
                        <button type="button" class="btn  btn-primary" wire:click="closeNotificationPopup" aria-label="Close">
                        &times;
                        </button>

                </div>

                <div class="card-body p-0">
                    <div class="scrollable-container" style="max-height: 400px; overflow-y: auto;"
                        id="notificationList">
                        @if (count($notifications) === 0)
                            <div class="text-center p-3">
                                <p class="text-muted">No notifications available.</p>
                            </div>
                        @endif
                        @foreach ($notifications as $notification)
                            @php
                                $typeIcon = match ($notification['type']) {
                                    'request_stock' => 'fa-box',
                                    'low_stock' => 'fa-triangle-exclamation',
                                    'new_order' => 'fa-cart-shopping',
                                    default => 'fa-bell',
                                };
                            @endphp

                            <a href="#" id="{{ $notification['id'] }}"
                                class="iq-sub-card open-form {{ $notification['status'] == 'unread' ? 'bg-light' : '' }}"
                                data-type="{{ $notification['type'] }}" data-id="{{ $notification['req_id'] }}"
                                data-nfid="{{ $notification['id'] }}">
                                <div class="d-flex align-items-start p-3 border-bottom">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center"
                                            style="width: 40px; height: 40px;">
                                            <i class="fas {{ $typeIcon }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="text-dark">
                                                {{ ucwords(str_replace('_', ' ', $notification['type'])) }}</h6>
                                        </div>
                                        <p class="small text-muted">{{ $notification['message'] }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small
                                                class="text-secondary"><strong>{{ $notification['time'] }}</strong></small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

    @endif

    <!-- Modal for Notification Detail -->
    @if ($selectedNotificationId && $notificationType)
        <div class="modal d-block" id="" tabindex="-1" role="dialog"
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Notification Details</h5>
                        <button type="button" class="close" wire:click="closeNotificationDetail">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        @if ($notificationType === 'expire_product')
                            @include('livewire.notification.expire-product-form', [
                                'expiredProducts' => $selectedNotificationData,
                            ])
                        @elseif ($notificationType === 'low_stock')
                            @include('livewire.notification.product-form', [
                                'data' => $selectedNotificationData,
                            ])
                        @elseif ($notificationType === 'approved_stock')
                            @include('livewire.notification.stock-approved-form', [
                                'stockRequest' => $selectedNotificationData,
                            ])
                        @elseif ($notificationType === 'transfer_stock')
                            @include('livewire.notification.stock-transfer-form', [
                                'stockTransfer' => $selectedNotificationData,
                            ])
                        @elseif ($notificationType === 'price_change')
                            @include('livewire.notification.price-change-form', [
                                'priceChange' => $selectedNotificationData,
                            ])
                        @else
                            @include('livewire.notification.product-form', [
                                'data' => $selectedNotificationData,
                            ])
                        @endif
                    </div>

                </div>
            </div>
        </div>
    @endif
    @push('scripts')
        <script>
            window.addEventListener('show-modal', event => {
                const modal = new bootstrap.Modal(document.getElementById('approveModal'));
                modal.show();
            });
        </script>
    @endpush

</div>
