<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LiquorHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="{{ asset('public/external/favicon.ico') }}" />
    <link rel="stylesheet" href="https://unpkg.com/animate.css@4.1.1/animate.css" />
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="stylesheet" href="{{ asset('index.css') }}">

    <!-- Fonts -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=STIX+Two+Text:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&amp;display=swap"
        data-tag="font" />
    <link rel="stylesheet" href="https://unpkg.com/@teleporthq/teleport-custom-scripts/dist/style.css" />

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fc;
        }

        .calculator button {
            width: 60px;
            height: 60px;
            margin: 5px;
            font-size: 20px;
        }

        .topbar {
            background-color: #fff;
            border-bottom: 1px solid #ccc;
            padding: 10px 20px;
        }

        .topbar .store-badge {
            background-color: #fd7e14;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 600;
        }

        .product-table td,
        .product-table th {
            vertical-align: middle;
        }

        .bottom-bar {
            background-color: #e9f2f9;
            padding: 10px 0;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                margin-bottom: 10px;
            }

            .main-screen-searchbar5 {
                width: 300px !important;
            }

            .calculator button {
                width: 22%;
            }

            .bottom-bar .col-md-4 {
                margin-bottom: 10px;
            }
        }

        .horizontal-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .main-screen-main-screen {
            min-width: 1024px;
        }
        .btn-light1{ 
            background-color:rgba(46, 158, 209, 1);
        }
       .blue-bg{ 
            background-color:rgba(200, 225, 245, 1);
        }
       
        .btn-hold {

        padding: 5px 24px;
        border-radius: 6px;
        justify-content: center;
        background-color: rgba(224, 142, 20, 1);
        color: rgba(255, 255, 255, 1);  
        }
        .btn-void {

        padding: 5px 24px;
        border-radius: 6px;
        justify-content: center;
        background-color: rgba(204, 68, 68, 1);
        color: rgba(255, 255, 255, 1);  
        }
        .btn-cash {

        padding: 5px 24px;
        border-radius: 6px;
        justify-content: center;
        background-color: rgba(73, 89, 144, 1);
        color: rgba(255, 255, 255, 1);  
        }
        .btn-online {

        padding: 5px 24px;
        border-radius: 6px;
        justify-content: center;
        background-color:rgba(46, 158, 209, 1);
        color: rgba(255, 255, 255, 1);  
        }
         .btn-cash-upi {

        border-radius: 6px;
        justify-content: center;
        background-color: rgba(0, 179, 179, 1);
        color: rgba(255, 255, 255, 1);  
        }
        .btn:hover {
        color: unset ! important;
        background-color: unset ! important;
        border-color: unset ! important;
        }
        .custom-border {
        
        border-color: rgba(0, 179, 179, 1);
        border-style: solid;
        border-width: 1px;
        border-radius: 28px;
        background-color: rgba(255, 255, 255, 1);
    }
    </style>
</head>

<body>
    @yield('page-content')
    <div class="modal fade bd-example-modal-lg" id="approveModal" tabindex="-1" role="dialog"
        aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="modalContent">
            </div>
        </div>
    </div>

    <!-- Backend Bundle JavaScript -->
    <script src="../assets/js/backend-bundle.min.js"></script>

    <!-- Table Treeview JavaScript -->
    <script src="../assets/js/table-treeview.js"></script>

    <!-- Chart Custom JavaScript -->
    <script src="../assets/js/customizer.js"></script>

    <!-- Chart Custom JavaScript -->
    <script async src="../assets/js/chart-custom.js"></script>

    <!-- app JavaScript -->
    <script src="../assets/js/app.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        setTimeout(function() {
            $('.toast').fadeOut('slow');
        }, 5000); // 5 seconds before fade-out


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

        function nfModelCls() {
            $('#approveModal').modal('hide');
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Observe all future buttons as well
            const attachClickAlert = (btn) => {
                btn.addEventListener('click', function(e) {
                    const now = new Date();
                    const hour = now.getHours();

                    if (hour >= 0 && hour < 6) {
                        //alert("It's after 12 AM!");
                        //return false;
                    }
                });
            };

            // Attach to existing buttons
            document.querySelectorAll('button').forEach(attachClickAlert);

            // Handle dynamically loaded buttons (Livewire updates)
            document.addEventListener("livewire:load", () => {
                Livewire.hook('message.processed', (message, component) => {
                    document.querySelectorAll('button').forEach(btn => {
                        if (!btn.dataset.hasMidnightAlert) {
                            attachClickAlert(btn);
                            btn.dataset.hasMidnightAlert = true; // Prevent double binding
                        }
                    });
                });
            });
        });
    </script>

    @livewireScripts

</body>

</html>
