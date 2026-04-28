<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LiquorHub</title>
    <link rel="shortcut icon" href="../assets/images/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/animate.css@4.1.1/animate.css" />
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="stylesheet" href="{{ asset('index.css') }}">
    <link rel="stylesheet" href="{{ asset('../assets/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('../assets/css/notification.css') }}">

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            border-radius: 50px !important;
            border: 1px solid #dee2e6 !important;
            padding: 4px 12px !important;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            padding-left: 4px !important;
            color: #333;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }

        .select2-dropdown {
            border-radius: 0.5rem !important;
            border: 1px solid #dee2e6 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 99999 !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #0d6efd !important;
        }

        .select2-search--dropdown .select2-search__field {
            border-radius: 4px !important;
            border: 1px solid #dee2e6 !important;
            padding: 6px 10px !important;
        }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    @livewireStyles
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

    <script async src="../assets/js/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS — after jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        window.addEventListener('loader-start', () => {
            const loader = document.getElementById('custom-loader');
            if (loader) {
                loader.classList.remove('d-none'); // remove Bootstrap's hidden class
                loader.style.display = 'flex'; // force visible
                console.log('Loader shown');
            }
        });

        window.addEventListener('loader-stop', () => {
            setTimeout(function() {
                // $('.toast').fadeOut('slow');
                console.log("dfgdfg");
            }, 5000); // 5 seconds before fade-out
            const loader = document.getElementById('custom-loader');
            if (loader) {
                loader.classList.add('d-none'); // optional, add back class
                loader.style.display = 'none'; // hide completely
                console.log('Loader hidden');
            }
        });

        // Optional: hide loader when Livewire is ready
        document.addEventListener('livewire:load', function() {
            const loader = document.getElementById('custom-loader');
            if (loader) {
                loader.classList.add('d-none');
                loader.style.display = 'none';
                console.log('Livewire loaded, loader removed');
            }
        });
    </script>

    @livewireScripts

    <!-- Global Loader -->
    <div id="custom-loader"
        class="d-none position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center"
        style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

</body>
</html>