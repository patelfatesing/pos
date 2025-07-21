<div>
    <!-- Notification Icon -->
    <button wire:click="togglePopup" class="btn position-relative">
        <img src="{{ asset('public/external/bell14471-yfps.svg') }}" alt="bell14471" class="main-screen-bell1" />
        @if ($readNotificationsCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $readNotificationsCount }}
            </span>
        @endif
    </button>

    <!-- Notification Popup -->
    @if ($showPopup)

        <div class="notification-screen-group400">
            <div class="notification-screen-group3741">
                <div class="notification-screen-group3731"></div>
            </div>
            <div class="notification-screen-group378">
                <div class="notification-screen-frame372">
                    <div class="notification-screen-group3742">
                        <div class="notification-screen-group3732">
                            <div class="notification-screen-frame373">
                                <div class="notification-screen-frame362">
                                    <div class="notification-screen-header">
                                        <span class="notification-screen-text84">
                                            Notifications (1)
                                        </span>
                                        <img src="{{ asset('public/external/image45185-z6hn-200h.png')}}" alt="image45185"
                                            class="notification-screen-image4" />
                                    </div>
                                </div>
                                <div class="notification-screen-frame371">
                                    <div class="notification-screen-notification1">
                                        <span class="notification-screen-text85">
                                            1 June 2025, 6:06:52 AM
                                        </span>
                                        <span class="notification-screen-text86">
                                            3 Min ago
                                        </span>
                                        <div class="notification-screen-frame3701">
                                            <div class="notification-screen-frame3">
                                                <div class="notification-screen-icon">
                                                    <img src="{{ asset('public/external/vector5125-khs6.svg')}}" alt="Vector5125"
                                                        class="notification-screen-vector26" />
                                                    <img src="{{ asset('public/external/vector5125-ghnd.svg')}}" alt="Vector5125"
                                                        class="notification-screen-vector27" />
                                                    <img src="{{ asset('public/external/vector5125-qywi.svg')}}" alt="Vector5125"
                                                        class="notification-screen-vector28" />
                                                    <img src="{{ asset('public/external/vector5125-yrst.svg')}}" alt="Vector5125"
                                                        class="notification-screen-vector29" />
                                                    <img src="{{ asset('public/external/vector5125-tre8.svg')}}" alt="Vector5125"
                                                        class="notification-screen-vector30" />
                                                </div>
                                            </div>
                                            <div class="notification-screen-frame3691">
                                                <span class="notification-screen-text87">
                                                    Low Stock
                                                </span>
                                                <span class="notification-screen-text88">
                                                    Some Products are Running Low!
                                                </span>
                                            </div>
                                        </div>
                                        <img src="{{ asset('public/external/rectangle4675146-844-200h.png')}}" alt="Rectangle4675146"
                                            class="notification-screen-rectangle4671" />
                                    </div>
                                    <div class="notification-screen-notification2">
                                        <div class="notification-screen-frame3702">
                                            <div class="notification-screen-layer13">
                                                <div class="notification-screen-group15">
                                                    <div class="notification-screen-group16">
                                                        <div class="notification-screen-group17">
                                                            <div class="notification-screen-group18">
                                                                <img src="{{ asset('public/external/vector5125-0v27.svg')}}"
                                                                    alt="Vector5125"
                                                                    class="notification-screen-vector31" />
                                                            </div>
                                                        </div>
                                                        <div class="notification-screen-group19">
                                                            <div class="notification-screen-group20">
                                                                <img src="{{ asset('public/external/vector5125-2nkr.svg')}}"
                                                                    alt="Vector5125"
                                                                    class="notification-screen-vector32" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="notification-screen-group21">
                                                        <div class="notification-screen-group22">
                                                            <img src="{{ asset('public/external/vector5125-zrhu.svg')}}"
                                                                alt="Vector5125" class="notification-screen-vector33" />
                                                        </div>
                                                        <div class="notification-screen-group23">
                                                            <img src="{{ asset('public/external/vector5125-3vv7.svg')}}"
                                                                alt="Vector5125" class="notification-screen-vector34" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="notification-screen-frame3692">
                                                <span class="notification-screen-text89">
                                                    Price Change
                                                </span>
                                                <span class="notification-screen-text90">
                                                    Products Price are Changed!
                                                </span>
                                            </div>
                                        </div>
                                        <img src="{{ asset('public/external/rectangle4675146-j0gl-200h.png')}}" alt="Rectangle4675146"
                                            class="notification-screen-rectangle4672" />
                                        <span class="notification-screen-text91">
                                            1 June 2025, 6:06:52 AM
                                        </span>
                                        <span class="notification-screen-text92">
                                            5 Min ago
                                        </span>
                                    </div>
                                    <div class="notification-screen-notification3">
                                        <div class="notification-screen-frame3703">
                                            <div class="notification-screen-layer14">
                                                <div class="notification-screen-group24">
                                                    <img src="{{ asset('public/external/vector5165-mkxn.svg')}}" alt="Vector5165"
                                                        class="notification-screen-vector35" />
                                                    <img src="{{ asset('public/external/vector5165-a0na.svg')}}" alt="Vector5165"
                                                        class="notification-screen-vector36" />
                                                </div>
                                            </div>
                                            <div class="notification-screen-frame3693">
                                                <span class="notification-screen-text93">
                                                    Transfer In
                                                </span>
                                                <span class="notification-screen-text94">
                                                    New Transfer Arrived!
                                                </span>
                                            </div>
                                        </div>
                                        <img src="{{ asset('public/external/rectangle4675146-6a3-200h.png')}}" alt="Rectangle4675146"
                                            class="notification-screen-rectangle4673" />
                                        <span class="notification-screen-text95">
                                            1 June 2025, 6:06:52 AM
                                        </span>
                                        <span class="notification-screen-text96">
                                            10 Min ago
                                        </span>
                                    </div>
                                    <div class="notification-screen-notification4">
                                        <div class="notification-screen-frame3704">
                                            <div class="notification-screen-layer15">
                                                <div class="notification-screen-group25">
                                                    <img src="{{ asset('public/external/vector5165-68pj.svg')}}" alt="Vector5165"
                                                        class="notification-screen-vector37" />
                                                    <img src="{{ asset('public/external/vector5165-fhw.svg')}}" alt="Vector5165"
                                                        class="notification-screen-vector38" />
                                                </div>
                                            </div>
                                            <div class="notification-screen-frame3694">
                                                <span class="notification-screen-text97">
                                                    Transfer Out
                                                </span>
                                                <span class="notification-screen-text98">
                                                    Your Transfer out!
                                                </span>
                                            </div>
                                        </div>
                                        <img src="{{ asset('public/external/rectangle4675146-vix6-200h.png')}}"
                                            alt="Rectangle4675146" class="notification-screen-rectangle4674" />
                                        <span class="notification-screen-text99">
                                            1 June 2025, 6:06:52 AM
                                        </span>
                                        <span class="notification-screen-text100">
                                            10 Min ago
                                        </span>
                                    </div>
                                    <div class="notification-screen-notification5">
                                        <div class="notification-screen-frame3705">
                                            <img src="{{ asset('public/external/image15185-oc6f-200h.png')}}" alt="image15185"
                                                class="notification-screen-image1" />
                                            <div class="notification-screen-frame3695">
                                                <span class="notification-screen-text101">
                                                    Expired product
                                                </span>
                                                <span class="notification-screen-text102">
                                                    Your Product is Expired!
                                                </span>
                                            </div>
                                        </div>
                                        <img src="{{ asset('public/external/rectangle4675146-yc2-200h.png')}}"
                                            alt="Rectangle4675146" class="notification-screen-rectangle4675" />
                                        <span class="notification-screen-text103">
                                            1 June 2025, 6:06:52 AM
                                        </span>
                                        <span class="notification-screen-text104">
                                            10 Min ago
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <img src="{{ asset('public/external/polygon14765-qc8d.svg')}}" alt="Polygon14765"
                    class="notification-screen-polygon1" />
            </div>
        </div>
        {{-- <div class="dropdown-menu dropdown-menu-end p-0 show iq-sub-dropdown" style="width: 360px;"
            aria-labelledby="dropdownMenuButton" id="notificationDropdown">
            <div class="card shadow-sm border-0 m-0">
                <div
                    class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3">
                    <h6 class="mb-0">Notifications <span class="badge bg-light text-primary"
                            id="all_notificationCount">{{ count($notifications) }}</span></h6>

                    <!-- Close button -->
                    <button type="button" class="btn  btn-primary" wire:click="closeNotificationPopup"
                        aria-label="Close">
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
                                    'low_stock' => 'fas fa-bell',
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
        </div> --}}

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
                                'from_store' => $from_store,
                                'to_store' => $to_store,
                                'transfer_type' => $transfer_type,
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
