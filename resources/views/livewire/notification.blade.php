<div>
    <!-- Notification Icon -->
    <button wire:click="togglePopup" class="btn btn-primary mr-1 position-relative">
        <i class="fas fa-bell"></i> <!-- Bell Icon -->
        @if(count($notifications) > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ count($notifications) }}
            </span>
        @endif
    </button>

    <!-- Notification Popup -->
    @if($showPopup)
        <div class="popup-notifications position-absolute top-100 end-0 p-3 bg-light shadow rounded">
            <h5>Notifications</h5>
            @foreach($notifications as $key => $notification)
                <div class="notification-item py-2" wire:click="viewNotificationDetail({{ $key }})" style="cursor: pointer;">
                    <p class="mb-1">{{ $notification['message'] }}</p>
                    <small class="text-muted">{{ $notification['time'] }}</small>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal for Notification Detail -->
    @if($selectedNotification)
        <div class="modal fade show" id="notificationModal" tabindex="-1" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Notification Detail</h5>
                        <button type="button" class="close" wire:click="closeNotificationDetail" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Message:</strong> {{ $selectedNotification['message'] }}</p>
                        <p><strong>Time:</strong> {{ $selectedNotification['time'] }}</p>
                        <p><strong>Details:</strong> {{ $selectedNotification['details'] ?? 'No additional details available.' }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeNotificationDetail">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" wire:click="closeNotificationDetail"></div>
    @endif
</div>
