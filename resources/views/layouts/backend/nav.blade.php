<?php
use App\Models\Branch;
$branch = Branch::where('is_deleted', 'no')->pluck('name', 'id');
?>

<style>
    .notification-wrapper {
        position: relative;
        display: inline-block;
        font-family: sans-serif;
    }

    .notification-icon {
        font-size: 24px;
        color: #333;
        cursor: pointer;
    }

    .notification-count {
        position: absolute;
        top: 4px;
        right: 0px;
        background-color: red;
        color: white;
        font-size: 12px;
        font-weight: bold;
        border-radius: 50%;
        padding: 2px 6px;
        min-width: 20px;
        text-align: center;
        line-height: 1;
        box-shadow: 0 0 0 2px white;
    }

    .scrollable-container {
        height: 400px;
        overflow-y: auto;
    }

.iq-sub-dropdown.notification-popup-custom {
    width: 412px !important;
    min-width: 412px !important;
    max-width: 412px !important;
    height: auto !important;
    max-height: 432px !important;
    padding: 0 !important;
    border: none !important;
    border-radius: 6px !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12) !important;
    overflow: hidden;
    background: #ffffff;
    flex-direction: column;
}

.iq-sub-dropdown.notification-popup-custom.show {
    display: flex !important;
}

/* Header (Height: 41px, Blue Color Matching #2E9ED1) */
.notification-custom-header {
    width: 100%;
    height: 41px;
    flex: 0 0 41px;
    background-color: #2e9ed1;
    display: flex;
    align-items: center;
    padding: 0 15px;
}

.notification-header-title {
    color: #ffffff;
    font-family: 'Open Sans', sans-serif;
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

/* Scrollable Container (flex-grows to fill remaining space) */
.notification-custom-scroll {
    flex: 1 1 auto;
    min-height: 200px;
    max-height: 341px;
    overflow-y: auto;
    overflow-x: hidden;
    background: #ffffff;
}

/* Row Item Box (Height: 70px) */
.notif-row-item {
    width: 100%;
    height: 70px;
    display: flex;
    position: relative;
    padding: 8px 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    text-decoration: none !important;
    cursor: pointer;
    box-sizing: border-box;
    align-items: center;
    background-color: #ffffff;
}

.notif-row-item:hover {
    filter: brightness(0.97);
}

.notif-row-item.msg_unread {
    background-color: #eaf4fb;
}

.notif-row-item.msg_unread .notif-text-title {
    color: #0f5f85;
}

.notif-row-item.msg_read {
    background-color: #ffffff;
}

.notif-icon-wrap {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    margin-right: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notif-icon-wrap img {
    width: 36px;
    height: 36px;
    object-fit: contain;
}

.notif-content-wrap {
    flex-grow: 1;
    max-width: 235px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
}

.notif-text-title {
    color: #1e1919;
    font-size: 16px;
    font-family: 'Open Sans', sans-serif;
    font-weight: 700;
    line-height: 16px;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.notif-text-msg {
    color: #1e1919;
    font-size: 12px;
    font-family: 'Open Sans', sans-serif;
    font-weight: 400;
    line-height: 16px;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Date and Time on the right side */
.notif-date-wrap {
    position: absolute;
    top: 8px;
    right: 12px;
    text-align: right;
    display: flex;
    flex-direction: column;
}

.notif-text-date {
    color: rgba(82, 74, 62, 0.82);
    font-size: 11px;
    font-weight: 600;
    font-family: 'Open Sans', sans-serif;
    white-space: nowrap;
    line-height: 14px;
}

.notif-text-time {
    color: rgba(82, 74, 62, 0.82);
    font-size: 10px;
    font-weight: 500;
    font-family: 'Open Sans', sans-serif;
    line-height: 14px;
    margin-top: 2px;
    white-space: nowrap;
}

/* Restored: Show All footer button (was missing in the new file) */
.notif-show-all-wrapper {
    flex: 0 0 auto;
    padding: 10px 12px 14px 12px;
    background: #ffffff;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.notif-show-all-btn {
    display: block;
    width: 100%;
    background-color: #2e9ed1;
    color: #ffffff !important;
    border: none;
    border-radius: 4px;
    padding: 1px 0;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
}

.notif-show-all-btn:hover {
    filter: brightness(0.93);
    color: #ffffff !important;
}
</style>
<div class="iq-top-navbar">
    <div class="iq-navbar-custom">
        <nav class="navbar navbar-expand-lg navbar-light p-0">
            <div class="iq-navbar-logo d-flex align-items-center justify-content-between">
                <i class="ri-menu-line wrapper-menu"></i>
                <a href="{{ route('dashboard') }}" class="header-logo">
                    <img src="{{ asset('assets/images/logo_ic.png') }}" class="img-fluid rounded-normal" alt="logo" />
                    <h4 class="logo-title">LiquorHub</h4>
                </a>
            </div>
            <div class="iq-search-bar device-search">
                {{-- <form action="#" class="searchbox">
                    <a class="search-link" href="#"><i class="ri-search-line"></i></a>
                    <input type="text" class="text search-input" placeholder="Search here..." />
                </form> --}}
            </div>
            <div class="d-flex align-items-center">
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-label="Toggle navigation">
                    <i class="ri-menu-3-line"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto navbar-list align-items-center">
                        @php
                            $currentStoreId = request()->route('store'); // or session('store_id') depending on how you manage it
                        @endphp

                        {{-- <li class="nav-item nav-icon dropdown mr-2">
                            <a href="#" class="dropdown-toggle btn border add-btn" id="dropdownMenuButton31"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="{{ asset('assets/images/small/store.png') }}" alt="store-icon"
                                    class="img-fluid image-flag mr-2" />
                                {{ $data['store'] ?? 'Select Store' }}
                            </a>

                            <div class="iq-sub-dropdown dropdown-menu" aria-labelledby="dropdownMenuButton31">
                                <div class="card shadow-none m-0">
                                    <div class="card-body p-3">
                                        <a class="iq-sub-card d-flex align-items-center "
                                            href="{{ route('dashboard') }}">
                                            <img src="{{ asset('assets/images/small/store.png') }}" alt="store-icon"
                                                class="img-fluid mr-2" style="width: 20px; height: 15px;" />
                                            Select Store
                                        </a>
                                        @foreach ($branch as $id => $name)
                                            <a class="iq-sub-card d-flex align-items-center {{ $currentStoreId == $id ? 'bg-primary text-white rounded' : '' }}"
                                                href="{{ route('dashboard.store', ['store' => $id]) }}">
                                                <img src="{{ asset('assets/images/small/' . ($name == 'Warehouse' ? 'icons8-warehouse-30.png' : 'store.png')) }}"
                                                    alt="store-icon" class="img-fluid mr-2"
                                                    style="width: 20px; height: 15px;" />
                                                {{ $name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </li> --}}
                        <!-- Date Filter Start -->
                        {{-- <li class="nav-item nav-icon d-flex align-items-center mt-4">
                            <form id="dateFilterForm" class="d-flex align-items-center" method="GET" action="{{ url()->current() }}" style="gap: 0.5rem;">
                                <div class="input-group input-group-sm">
                                    <input 
                                        type="date" 
                                        name="start_date" 
                                        id="start_date" 
                                        class="form-control border-left-0" 
                                        value="{{ request('start_date') }}" 
                                        style="min-width: 130px; height: 38px;">
                                </div>
                                <span class="mx-1">to</span>
                                <div class="input-group input-group-sm">
                                    <input 
                                        type="date" 
                                        name="end_date" 
                                        id="end_date" 
                                        class="form-control border-left-0" 
                                        value="{{ request('end_date') }}" 
                                        style="min-width: 130px; height: 38px;">
                                </div>
                                <div class="btn-group" role="group" aria-label="Date filter actions" style="height: 38px;">
                                    <button 
                                        type="submit" 
                                        class="btn btn-sm btn-outline-primary d-flex align-items-center px-3"
                                        title="Apply date filter">
                                        <i class="ri-filter-line mr-1"></i> Filter
                                    </button>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-outline-secondary d-flex align-items-center px-3"
                                        id="clearDateFilter"
                                        title="Clear date filter">
                                        <i class="ri-close-line mr-1"></i> 
                                    </button>
                                </div>
                            </form>
                        </li> --}}

                        <!-- Date Filter End -->
                        <li class="nav-item nav-icon dropdown">
                            {{-- <a href="#" class="search-toggle dropdown-toggle btn border add-btn"
                                id="dropdownMenuButton02" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                @if (session('locale') == 'hi')
                                    <img src="{{ asset('assets/images/small/india.png') }}" alt="img-flag"
                                        class="img-fluid image-flag mr-2" style="width: 20px; height: 15px;" />हिंदी
                                @else
                                    <img src="{{ asset('assets/images/small/flag-01.png') }}" alt="img-flag"
                                        class="img-fluid image-flag mr-2" />English
                                @endif
                            </a> --}}
                            <div class="iq-sub-dropdown dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                <div class="card shadow-none m-0">
                                    <div class="card-body p-3">
                                        <a class="iq-sub-card" href="{{ url('lang/en') }}">
                                            <img src="{{ asset('assets/images/small/flag-01.png') }}" alt="img-flag"
                                                class="img-fluid mr-2" style="width: 20px; height: 15px;" />English
                                        </a>
                                        <a class="iq-sub-card" href="{{ url('lang/hi') }}">
                                            <img src="{{ asset('assets/images/small/india.png') }}" alt="img-flag"
                                                class="img-fluid mr-2" style="width: 20px; height: 15px;" />हिंदी
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <a href="javascript:void(0);" 
                            class="btn border add-btn shadow-none mx-1 d-flex align-items-center" 
                            id="btnRefreshPage" 
                            title="Refresh Page"
                            onclick="window.location.reload();">
                                Refresh
                            </a>
                        </li>
                        <li>
                            <a href="#" class="btn border add-btn shadow-none mx-2 d-none d-md-block"
                                data-toggle="modal" data-target="#new-order">{{ session('role_name') }}</a>
                        </li>
                        <li class="nav-item nav-icon search-content">
                            <a href="#" class="search-toggle rounded" id="dropdownSearch" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="ri-search-line"></i>
                            </a>
                            <div class="iq-search-bar iq-sub-dropdown dropdown-menu" aria-labelledby="dropdownSearch">
                                <form action="#" class="searchbox p-2">
                                    <div class="form-group mb-0 position-relative">
                                        <input type="text" class="text search-input font-size-12"
                                            placeholder="type here to search..." />
                                        <a href="#" class="search-link"><i class="las la-search"></i></a>
                                    </div>
                                </form>
                            </div>
                        </li>
                        <li class="nav-item nav-icon dropdown">

                            <?php
                            $getNotification = getNotificationsByNotifyTo(Auth::id(), null, 10);
                            $getCount = collect($getNotification)->where('status', 'unread')->count();

                            $getTotalCount = count($getNotification);
                            $user = Auth::user();
                            ?>

                            <div class="iq-sub-dropdown dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                <div class="card shadow-none m-0">
                                    <div class="card-body p-0">
                                        <div class="cust-title p-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h5 class="mb-0">All Messages</h5>
                                                <a class="badge badge-primary badge-card" href="#">3</a>
                                            </div>
                                        </div>
                                        <div class="px-3 pt-0 pb-0 sub-card">
                                            <a href="#" class="iq-sub-card">
                                                <div class="media align-items-center cust-card py-3 border-bottom">
                                                    <div class="">
                                                        <img class="avatar-50 rounded-small"
                                                            src="{{ asset('assets/images/user/01.jpg') }}"
                                                            alt="01" />
                                                    </div>
                                                    <div class="media-body ml-3">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <h6 class="mb-0">Emma Watson</h6>
                                                            <small class="text-dark"><b>12 : 47 pm</b></small>
                                                        </div>
                                                        <small class="mb-0">Lorem ipsum dolor sit amet</small>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#" class="iq-sub-card">
                                                <div class="media align-items-center cust-card py-3 border-bottom">
                                                    <div class="">
                                                        <img class="avatar-50 rounded-small"
                                                            src="{{ asset('assets/images/user/02.jpg') }}"
                                                            alt="02" />
                                                    </div>
                                                    <div class="media-body ml-3">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <h6 class="mb-0">Ashlynn Franci</h6>
                                                            <small class="text-dark"><b>11 : 30 pm</b></small>
                                                        </div>
                                                        <small class="mb-0">Lorem ipsum dolor sit amet</small>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#" class="iq-sub-card">
                                                <div class="media align-items-center cust-card py-3">
                                                    <div class="">
                                                        <img class="avatar-50 rounded-small"
                                                            src="{{ asset('assets/images/user/03.jpg') }}"
                                                            alt="03" />
                                                    </div>
                                                    <div class="media-body ml-3">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <h6 class="mb-0">Kianna Carder</h6>
                                                            <small class="text-dark"><b>11 : 21 pm</b></small>
                                                        </div>
                                                        <small class="mb-0">Lorem ipsum dolor sit amet</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <a class="right-ic btn btn-success btn-block position-relative p-2"
                                            href="#" role="button">
                                            View All
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item nav-icon dropdown">

                            <a href="#" class="search-toggle dropdown-toggle notification-wrapper"
                                id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell notification-icon"></i>
                                <div class="notification-count" id="all_unread_notificationCount">{{ $getCount }}</div>
                            </a>

                            <div class="iq-sub-dropdown dropdown-menu notification-popup-custom dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                <div class="notification-custom-header">
                                    <span class="notification-header-title">
                                        Notifications (<span id="all_notificationCount">{{ $getTotalCount }}</span>)
                                    </span>
                                </div>

                                <!-- Scrollable Body -->
                                <div class="notification-custom-scroll" id="notificationList">
                                </div>

                                <!-- Restored from old file: Show All footer, hidden until fetchNotifications decides to show it -->
                                <div id="showAllWrapper" class="notif-show-all-wrapper" style="display: none;">
                                    <a href="{{ route('notifications.index') }}" class="notif-show-all-btn text-center">Show All</a>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item nav-icon dropdown caption-content">
                            <a href="#" class="search-toggle dropdown-toggle" id="dropdownMenuButton4"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="{{ asset('assets/images/user/1.png') }}" class="img-fluid rounded"
                                    alt="user" />
                            </a>
                            <div class="iq-sub-dropdown dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <div class="card shadow-none m-0">
                                    <div class="card-body p-0 text-center">
                                        <div class="media-body profile-detail text-center">
                                            <img src="{{ asset('assets/images/page-img/shop.jpg') }}"
                                                alt="profile-bg" class="rounded-top img-fluid mb-4" />
                                            <img src="{{ asset('assets/images/user/1.png') }}" alt="profile-img"
                                                class="rounded profile-img img-fluid avatar-70" />
                                        </div>
                                        <div class="p-3">
                                            <h5 class="mb-1">{{ @$user->userInfo->first_name }}
                                            </h5>
                                            <p class="mb-0">Since
                                                {{ \Carbon\Carbon::parse(Auth::user()->created_at)->format('d F, Y') }}
                                            </p>
                                            <div class="d-flex align-items-center justify-content-center mt-3">
                                                <a href="{{ route('profile.edit') }}"
                                                    class="btn border mr-2">Profile</a>


                                                <form method="POST" class="mb0" action="{{ route('logout') }}">
                                                    @csrf

                                                    <a :href="route('logout')" class="btn border"
                                                        onclick="event.preventDefault();
                                                                        this.closest('form').submit();">
                                                        {{ __('Log Out') }}
                                                    </a>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="modal fade bd-example-modal-lg" id="approveModal" tabindex="-1" role="dialog"
    aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 45%;">
        <div class="modal-content" id="modalContent">
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/jquery-3.6.0.min.js')}}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/7.2.0/pusher.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    
    $(document).on('click', '.open-form', function() {
        let type = $(this).data('type');
        let id = $(this).data('id');
        let nfid = $(this).data('nfid');
        let id_get = $(this).attr('id');

        let get_tc = parseInt($(".notification-count").text()); // get current count

        $.ajax({
            url: '/popup/form/' + type + "?id=" + id + "&nfid=" + nfid,
            type: 'GET',
            success: function(response) {
                $("#" + id_get).removeClass("msg_unread").addClass("msg_read");

                if (get_tc > 0) {
                    get_tc = get_tc - 1;
                }
                $(".notification-count").text(get_tc);

                $('#modalContent').html(response);
                var modal = new bootstrap.Modal(document.getElementById('approveModal'));
                modal.show();
                // $('#approveModal').modal('show');
            },
            error: function() {
                alert('Failed to load form.');
            }
        });
    });

    // Optional: Close modal on background click
    $(document).on('click', '#popupModal', function(e) {
        if (e.target === this) {
            $(this).fadeOut();
        }
    });

    var pusher = new Pusher("{{ config('broadcasting.connections.pusher.key') }}", {
        cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
        encrypted: true,
    });

    var channel = pusher.subscribe('drawer-channel');

    channel.bind('DrawerOpened', function(data) {
        if (data.notify_to == null) {
            Swal.fire({
                title: '📢 New Notification!',
                text: `${data.message} (Notify By: ${data.customer})`,
                icon: 'info',
                confirmButtonText: 'Okay'
            }).then((result) => {
                if (result.isConfirmed) {
                    // This code runs when "Okay" is clicked
                    // console.log('User clicked Okay');

                    // $.ajax({
                    //     url: '/popup/form/' + data.type + "?id=" + data.value,
                    //     type: 'GET',
                    //     success: function(response) {
                    //         $('#modalContent').html(response);
                    //         $('#approveModal').modal('show');
                    //     },
                    //     error: function() {
                    //         alert('Failed to load form.');
                    //     }
                    // });
                }
            });
        }
    });

    function fetchNotifications() {
        fetch('{{ route('notifications.get-notication') }}')
            .then(response => response.json())
            .then(data => {
                let get_data = data.data;

                $("#all_unread_notificationCount").text(data.res_all_unread);
                $("#all_notificationCount").text(data.res_all);

                const showAllWrapperEl = document.getElementById('showAllWrapper');
                if (showAllWrapperEl) {
                    showAllWrapperEl.style.display = 'block';
                }

                const container = document.getElementById("notificationList");
                container.innerHTML = ''; // Clear existing content

                if (!get_data || get_data.length === 0) {
                    container.innerHTML = '<div class="text-center p-3 text-muted" style="font-size: 12px;">No notifications available.</div>';
                    return;
                }

                // Bell icon image URL
                const bellIconUrl = "{{ asset('external/bell14471-yfps.svg') }}";

                get_data.forEach(item => {
                    let id = item.id || '';
                    const typeClass = 'notif-type-' + (item.type || 'default');
                    const typeTitle = item.type ? item.type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : 'Notification';

                    // Restored from old file: unread/read class based on status
                    const isRead = item.status === 'read' ? 'msg_read' : 'msg_unread';

                    const dt = new Date(item.created_at);
                    const formattedDate = dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                    const formattedTime = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });

                    const redirectUrl = item.type === 'expire_product' ? `${data.url}/${item.id}` : '#';

                    let html = `
                        <div class="notif-row-item open-form ${typeClass} ${isRead}" 
                             id="${id}" 
                             data-type="${item.type}" 
                             data-id="${item.id}" 
                             data-nfid="${item.id}"
                             onclick="${item.type === 'expire_product' ? `window.location.href='${redirectUrl}'` : ''}">
                            
                            <div class="notif-icon-wrap">
                                <img src="${bellIconUrl}" alt="Notification Icon" />
                            </div>

                            <div class="notif-content-wrap">
                                <span class="notif-text-title">${typeTitle}</span>
                                <p class="notif-text-msg">${item.content || item.message || ''}</p>
                                <input type="hidden" value="${id}" name="id" />
                            </div>

                            <div class="notif-date-wrap">
                                <span class="notif-text-date">${formattedDate}</span>
                                <span class="notif-text-time">${formattedTime}</span>
                            </div>
                        </div>
                    `;

                    container.insertAdjacentHTML('beforeend', html);
                });
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
            });
    }
</script>

@if (Auth::check())
    <script>
        // Call every 30 seconds
        setInterval(fetchNotifications, 3000);
        // Fetch immediately on page load
        fetchNotifications();
    </script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const clearBtn = document.getElementById('clearDateFilter');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                document.getElementById('start_date').value = '';
                document.getElementById('end_date').value = '';
                document.getElementById('dateFilterForm').submit();
            });
        }
    });
</script>