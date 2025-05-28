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
</style>
<div class="iq-top-navbar">
    <div class="iq-navbar-custom">
        <nav class="navbar navbar-expand-lg navbar-light p-0">
            <div class="iq-navbar-logo d-flex align-items-center justify-content-between">
                <i class="ri-menu-line wrapper-menu"></i>
                <a href="{{ route('dashboard') }}" class="header-logo">
                    <img src="{{ asset('assets/images/logo.png') }}" class="img-fluid rounded-normal" alt="logo" />
                    <h5 class="logo-title ml-3">LiquorHub</h5>
                </a>
            </div>
            <div class="iq-search-bar device-search">
                <form action="#" class="searchbox">
                    <a class="search-link" href="#"><i class="ri-search-line"></i></a>
                    <input type="text" class="text search-input" placeholder="Search here..." />
                </form>
            </div>
            <div class="d-flex align-items-center">
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-label="Toggle navigation">
                    <i class="ri-menu-3-line"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto navbar-list align-items-center">
                        <li class="nav-item nav-icon dropdown">
                            <a href="#" class="search-toggle dropdown-toggle btn border add-btn"
                                id="dropdownMenuButton31" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <img src="{{ asset('assets/images/small/store.png') }}" alt="img-flag"
                                    class="img-fluid image-flag mr-2" />Select Store

                            </a>
                            <div class="iq-sub-dropdown dropdown-menu" aria-labelledby="dropdownMenuButton31">
                                <div class="card shadow-none m-0">
                                    <div class="card-body p-3">
                                        @foreach ($branch as $id => $name)
                                            @if ($name == 'Warehouse')
                                                <a class="iq-sub-card" href="{{ url('lang/en') }}">
                                                    <img src="{{ asset('assets/images/small/icons8-warehouse-30.png') }}"
                                                        alt="img-flag" class="img-fluid mr-2"
                                                        style="width: 20px; height: 15px;" />{{ $name }}
                                                </a>
                                            @else
                                                <a class="iq-sub-card" href="{{ url('lang/en') }}">
                                                    <img src="{{ asset('assets/images/small/store.png') }}"
                                                        alt="img-flag" class="img-fluid mr-2"
                                                        style="width: 20px; height: 15px;" />{{ $name }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li class="nav-item nav-icon dropdown">
                            <a href="#" class="search-toggle dropdown-toggle btn border add-btn"
                                id="dropdownMenuButton02" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                @if (session('locale') == 'hi')
                                    <img src="{{ asset('assets/images/small/india.png') }}" alt="img-flag"
                                        class="img-fluid image-flag mr-2" style="width: 20px; height: 15px;" />हिंदी
                                @else
                                    <img src="{{ asset('assets/images/small/flag-01.png') }}" alt="img-flag"
                                        class="img-fluid image-flag mr-2" />English
                                @endif
                            </a>
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
                                        <a class="right-ic btn btn-primary btn-block position-relative p-2"
                                            href="#" role="button">
                                            View All
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item nav-icon dropdown">

                            <a href="#" class="search-toggle dropdown-toggle notification-wrapper"
                                id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="fas fa-bell notification-icon"></i>
                                <div class="notification-count" id="all_unread_notificationCount">{{ $getCount }}
                                </div>
                                <span class="bg-primary"></span>
                            </a>
                            <div class="iq-sub-dropdown dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <div class="card shadow-none m-0">
                                    <div class="card-body p-0">
                                        <div class="cust-title p-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h5 class="mb-0">Notifications</h5>
                                                <a class="badge badge-primary badge-card" id="all_notificationCount"
                                                    href="#">{{ $getTotalCount }}</a>
                                            </div>
                                        </div>
                                        <div class="px-3 pt-0 pb-0 sub-card scrollable-container"
                                            id="notificationList"></div>
                                        <div id="showAllWrapper" class="text-center mt-2" style="display: none;">
                                            <a href="{{ route('notifications.index') }}"
                                                class="btn btn-sm btn-primary">Show All</a>
                                        </div>


                                        {{-- <a class="right-ic btn btn-primary btn-block position-relative p-2"
                                            href="#" role="button">
                                            View All
                                        </a> --}}
                                    </div>
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
                                                {{ @$user->userInfo->last_name }}</h5>
                                            <p class="mb-0">Since
                                                {{ \Carbon\Carbon::parse(Auth::user()->created_at)->format('d F, Y') }}
                                            </p>
                                            <div class="d-flex align-items-center justify-content-center mt-3">
                                                <a href="{{ route('profile.edit') }}"
                                                    class="btn border mr-2">Profile</a>


                                                <form method="POST" action="{{ route('logout') }}">
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="modalContent">
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/7.2.0/pusher.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).on('click', '.open-form', function() {
        let type = $(this).data('type');

        let id = $(this).data('id');

        let nfid = $(this).data('nfid');
        let id_get = $(this).attr('id');

        let get_tc = parseInt($(".notification-count").text()); // get current cou

        // console.log(get_tc,"==get_tc");
        $.ajax({
            url: '/popup/form/' + type + "?id=" + id + "&nfid=" + nfid,
            type: 'GET',
            success: function(response) {
                $("#" + id_get).removeClass("iq-sub-card open-form mb-1 msg_unread");
                $("#" + id_get).addClass("iq-sub-card open-form mb-1 msg_read");


                if (get_tc > 0) {
                    get_tc = get_tc - 1;
                }
                $(".notification-count").text(get_tc);

                $('#modalContent').html(response);

                $('#approveModal').modal('show');
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
                    console.log('User clicked Okay');

                    $.ajax({
                        url: '/popup/form/' + data.type + "?id=" + data.value + "&nfid=" + data
                            .nfid,
                        type: 'GET',
                        success: function(response) {

                            $('#modalContent').html(response);

                            $('#approveModal').modal('show');
                        },
                        error: function() {
                            alert('Failed to load form.');
                        }
                    });
                }
            });
        }
    });

    function fetchNotifications() {
        fetch('{{ route('notifications.get-notication') }}')
            .then(response => response.json())
            .then(data => {

                let get_data = data.data;
                let all_count = data.res_all;

                // Display or hide "Show All" button
                if (data.res_all > 10) {
                    showAllWrapper.style.display = 'block';
                } else {
                    showAllWrapper.style.display = 'none';
                }

                document.getElementById("all_notificationCount").innerText = data.res_all_unread;
                document.getElementById("all_notificationCount").innerText = data.res_all;
                const container = document.getElementById("notificationList");
                container.innerHTML = ''; // Clear existing content

                get_data.forEach(item => {
                    let id = '';
                    if (item.details) {
                        try {
                            const parsedDetails = JSON.parse(item.details);
                            id = parsedDetails.id || '';
                        } catch (e) {
                            console.error('Error parsing details:', e);
                        }
                    }

                    const isRead = item.status === 'read' ? 'msg_read' : 'msg_unread';
                    const type = item.type.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
                    const createdAt = new Date(item.created_at);
                    const formattedDate = createdAt.toLocaleString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });

                    const html = `
                    <a href="#" data-id="${id}" class="iq-sub-card open-form mb-1 ${isRead}"
                        data-type="${item.type}" id="${item.id}" data-nfid="${item.id}">
                        <div class="media align-items-center cust-card py-3 border-bottom">
                            <div>
                                <img class="avatar-50 rounded-small"  src="{{ asset('assets/images/user/notification.png') }}" alt="Notification">
                               </div>
                            <div class="media-body ml-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0">${type}</h6>
                                </div>
                                <input type="hidden" value="${id}" name="id" />
                                <small class="mb-0 mt-1 mb-1">${item.content}</small>
                                <div class="d-flex align-items-center justify-content-between">
                                    <small class="text-dark"><b>${formattedDate}</b></small>
                                </div>
                            </div>
                        </div>
                    </a>
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
