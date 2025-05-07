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
        <div class="popup-notifications position-absolute top-100 end-0 p-3 card shadow-none m-0 shadow rounded"
            style="z-index: 1050;">
            <h5>Notifications</h5>
            @foreach ($notifications as $notification)
                @if ($notification['status'] == 'unread')
                    <div class="notification-item py-2 bg-light rounded p-2"
                        wire:click="viewNotificationDetail({{ $notification['notify_to'] }}, '{{ $notification['type'] }}', '{{ $notification['req_id'] }}','{{ $notification['id'] }}')"
                        style="cursor: pointer;">
                        <p class="mb-1">{{ $notification['message'] }}</p>
                        <small class="text-muted">{{ $notification['time'] }}</small>
                    </div>
                @else
                    <div class="notification-item py-2"
                        wire:click="viewNotificationDetail({{ $notification['notify_to'] }}, '{{ $notification['type'] }}', '{{ $notification['req_id'] }}','{{ $notification['id'] }}')"
                        style="cursor: pointer;">
                        <p class="mb-1">{{ $notification['message'] }}</p>
                        <small class="text-muted">{{ $notification['time'] }}</small>
                    </div>
                @endif
            @endforeach
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
