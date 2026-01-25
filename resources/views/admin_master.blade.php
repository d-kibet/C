<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Raha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Premium Multipurpose Admin & Dashboard Template" />
    <meta name="author" content="Themesdesign" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" href="{{ asset('backend/assets/images/Raha_logo.ico') }}">

    <!-- CSS Assets -->
    <!-- Vector Map CSS -->
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}" type="text/css" />
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" type="text/css" />
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('backend/assets/css/bootstrap.min.css') }}" id="bootstrap-style" type="text/css" />
    <!-- Icons CSS -->
    <link rel="stylesheet" href="{{ asset('backend/assets/css/icons.min.css') }}" type="text/css" />
    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('backend/assets/css/app.min.css') }}" id="app-style" type="text/css" />
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" type="text/css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
          integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom CSS for Sticky Footer -->
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        /* Wrap all content in a relative container */
        #layout-wrapper {
            position: relative;
            min-height: 100vh;
            /* Add bottom padding equal to footer height to prevent overlap */
            padding-bottom: 60px; /* Adjust if your footer height changes */
        }
        /* Footer styling */
        .footer {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 60px; /* Footer height */
            background: #f8f9fa;
            padding: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body data-topbar="dark" style="padding-top: 10px;">
    <!-- Wrapper for the whole page -->
    <div id="layout-wrapper">
        @include('admin.body.header')
        @include('admin.body.sidebar')

        <!-- Main Content -->
        <div class="main-content">
            @yield('admin')
        </div>
        <!-- End Main Content -->

        <!-- Footer (stays at the bottom of #layout-wrapper) -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        CodeLupus ©
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <!-- END layout-wrapper -->

    <!-- Right Sidebar Overlay -->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT Assets -->
    <!-- jQuery (must be loaded first) -->
    <script src="{{ asset('backend/assets/libs/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap Bundle -->
    <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- MetisMenu, SimpleBar, Node Waves -->
    <script src="{{ asset('backend/assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/node-waves/waves.min.js') }}"></script>
    <!-- DataTables JS -->
    <script src="{{ asset('backend/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- App JS -->
    <script src="{{ asset('backend/assets/js/app.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                order: [[0, 'desc']],
                responsive: true
            });
        });
    </script>
    <!-- Additional Plugins -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="{{ asset('backend/assetss/js/code.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>

        var sessionLifetime = 1200; // 30 minutes
        var warningTime = 300; // Warn 5 minutes before expiry


        var warningCountdown = sessionLifetime - warningTime;


        setTimeout(function(){
            Swal.fire({
                title: 'Session Expiring Soon',
                text: 'Your session will expire in 5 minutes. Click "Refresh Session" to extend your session.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Refresh Session',
                cancelButtonText: 'Logout'
            }).then(function(result){
                if (result.isConfirmed) {

                    location.reload();
                } else {

                    window.location.href = '/login';
                }
            });
        }, warningCountdown * 1000);


        setTimeout(function(){
            Swal.fire({
                title: 'Session Expired',
                text: 'Your session has expired. Please log in again.',
                icon: 'error',
                confirmButtonText: 'Login'
            }).then(function(){
                window.location.href = '/login';
            });
        }, sessionLifetime * 1000);
        </script>


   <script>
        @if(Session::has('message'))
            var type = "{{ Session::get('alert-type','info') }}";
            switch(type){
                case 'info':
                    toastr.info("{{ Session::get('message') }}");
                    break;
                case 'success':
                    toastr.success("{{ Session::get('message') }}");
                    break;
                case 'warning':
                    toastr.warning("{{ Session::get('message') }}");
                    break;
                case 'error':
                    toastr.error("{{ Session::get('message') }}");
                    break;
            }
        @endif
    </script>

    <!-- Notification System JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Only load notifications if we're on an authenticated page
        if (document.getElementById('notificationDropdown')) {
            loadNotifications();

            // Load notifications every 30 seconds
            setInterval(loadNotifications, 30000);

            // Load notifications when dropdown is clicked
            document.getElementById('notificationDropdown').addEventListener('click', function() {
                loadNotifications();
            });
        }
    });

    function loadNotifications() {
        fetch('/notifications/unread')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load notifications');
                }
                return response.json();
            })
            .then(data => {
                updateNotificationBadge(data.count);
                updateNotificationList(data.notifications);
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                // Show error state in dropdown
                const list = document.getElementById('notificationList');
                if (list) {
                    list.innerHTML = `
                        <div class="text-center p-4">
                            <i class="ri-error-warning-line text-danger" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">Failed to load notifications</p>
                        </div>
                    `;
                }
            });
    }

    function updateNotificationBadge(count) {
        const badge = document.getElementById('notificationBadge');
        const pulse = document.getElementById('notificationPulse');
        const bell = document.getElementById('notificationDropdown');

        // Safety check: ensure all elements exist before accessing them
        if (!badge || !pulse || !bell) {
            return;
        }

        if (count > 0) {
            // Show badge and pulse
            badge.style.display = 'flex';
            pulse.style.display = 'block';
            badge.textContent = count > 99 ? '99+' : count;

            // Add shake animation for new notifications
            bell.classList.add('notification-bell-shake');
            setTimeout(() => {
                bell.classList.remove('notification-bell-shake');
            }, 1000);

            // Add urgent class for high counts
            if (count > 10) {
                bell.classList.add('notification-urgent');
            } else {
                bell.classList.remove('notification-urgent');
            }

            // Add gradient color based on count with enhanced visibility
            if (count > 10) {
                badge.style.background = 'linear-gradient(135deg, #dc3545, #b02a37)';
                badge.style.boxShadow = '0 3px 8px rgba(220, 53, 69, 0.5), 0 0 0 1px rgba(220, 53, 69, 0.2)';
                pulse.style.backgroundColor = '#dc3545';
                pulse.style.boxShadow = '0 0 15px rgba(220, 53, 69, 0.6)';
            } else if (count > 5) {
                badge.style.background = 'linear-gradient(135deg, #fd7e14, #dc6502)';
                badge.style.boxShadow = '0 3px 8px rgba(253, 126, 20, 0.5), 0 0 0 1px rgba(253, 126, 20, 0.2)';
                pulse.style.backgroundColor = '#fd7e14';
                pulse.style.boxShadow = '0 0 15px rgba(253, 126, 20, 0.6)';
            } else {
                badge.style.background = 'linear-gradient(135deg, #198754, #146c43)';
                badge.style.boxShadow = '0 3px 8px rgba(25, 135, 84, 0.5), 0 0 0 1px rgba(25, 135, 84, 0.2)';
                pulse.style.backgroundColor = '#198754';
                pulse.style.boxShadow = '0 0 15px rgba(25, 135, 84, 0.6)';
            }
        } else {
            badge.style.display = 'none';
            pulse.style.display = 'none';
            bell.classList.remove('notification-urgent');
        }
    }

    function updateNotificationList(notifications) {
        const list = document.getElementById('notificationList');

        // Safety check: ensure element exists
        if (!list) {
            return;
        }

        if (notifications.length === 0) {
            list.innerHTML = `
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="ri-notification-off-line text-muted" style="font-size: 2.5rem;"></i>
                    </div>
                    <h6 class="text-muted">No new notifications</h6>
                    <p class="text-muted mb-0 small">You're all caught up!</p>
                </div>
            `;
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            const iconClass = notification.data.type === 'overdue_delivery' ?
                'ri-time-line text-warning' : 'ri-notification-3-line text-info';

            const actionUrl = notification.data.action_url || '#';
            const hasAction = notification.data.action_url;

            // Truncate long messages
            const message = notification.message.length > 60 ?
                notification.message.substring(0, 60) + '...' : notification.message;

            html += `
                <div class="notification-item">
                    <div class="d-flex align-items-start p-3 border-bottom">
                        <div class="notification-icon me-3 flex-shrink-0">
                            <div class="icon-circle">
                                <i class="${iconClass} fs-6"></i>
                            </div>
                        </div>
                        <div class="notification-content flex-grow-1 min-width-0">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <p class="notification-message mb-0 text-truncate">${message}</p>
                            </div>
                            <small class="text-muted d-block mb-2">${notification.created_at}</small>
                            <div class="notification-actions">
                                ${hasAction ? `<button class="btn btn-sm btn-primary me-1" onclick="markAsReadAndRedirect('${notification.id}', '${actionUrl}', event)"><i class="ri-eye-line"></i></button>` : ''}
                                <button class="btn btn-sm btn-outline-secondary" onclick="markNotificationAsRead('${notification.id}', event)" title="Mark as read"><i class="ri-check-line"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        // Add "View All" link if there are more than 5 notifications
        if (notifications.length >= 5) {
            html += `
                <div class="text-center p-2 border-top">
                    <a href="/notifications" class="btn btn-sm btn-link text-decoration-none">
                        <i class="ri-arrow-right-circle-line"></i> View all notifications
                    </a>
                </div>
            `;
        }

        list.innerHTML = html;
    }

    function markNotificationAsRead(notificationId, event) {
        event.preventDefault();

        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload notifications to update the count
                loadNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    function markAsReadAndRedirect(notificationId, actionUrl, event) {
        event.preventDefault();

        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to the action URL
                window.location.href = actionUrl;
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }
    </script>

    <!-- Notification Styles -->
    <style>
    /* Enhanced Notification Bell Styling */
    .notification-bell {
        position: relative;
        border: none;
        background: transparent;
        padding: 0.85rem;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
    }

    .notification-bell:hover {
        background: rgba(13, 110, 253, 0.1);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    }

    .notification-bell-container {
        position: relative;
        display: inline-block;
    }

    .notification-bell-icon {
        font-size: 1.4rem;
        color: #0d6efd;
        transition: all 0.3s ease;
        font-weight: 600;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .notification-bell:hover .notification-bell-icon {
        color: #0b5ed7;
        transform: rotate(15deg) scale(1.1);
        text-shadow: 0 2px 4px rgba(11, 94, 215, 0.3);
    }

    .notification-badge {
        position: absolute;
        top: -10px;
        right: -10px;
        min-width: 22px;
        height: 22px;
        background: linear-gradient(135deg, #dc3545, #b02a37);
        color: white;
        border-radius: 50%;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #fff;
        box-shadow: 0 3px 8px rgba(220, 53, 69, 0.4), 0 0 0 1px rgba(220, 53, 69, 0.1);
        animation: notification-bounce 0.6s ease-in-out;
        z-index: 10;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    .notification-pulse {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background-color: #dc3545;
        opacity: 0.8;
        animation: notification-pulse 2s infinite;
        z-index: 5;
        box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
    }

    @keyframes notification-bounce {
        0%, 20%, 60%, 100% {
            transform: translateY(0) scale(1);
        }
        40% {
            transform: translateY(-4px) scale(1.15);
        }
        80% {
            transform: translateY(-2px) scale(1.08);
        }
    }

    @keyframes notification-pulse {
        0% {
            transform: scale(1);
            opacity: 0.8;
        }
        50% {
            transform: scale(1.6);
            opacity: 0.4;
        }
        100% {
            transform: scale(2.2);
            opacity: 0;
        }
    }

    /* Bell shake animation for new notifications */
    @keyframes notification-shake {
        0%, 100% { transform: rotate(0deg) scale(1); }
        10% { transform: rotate(12deg) scale(1.05); }
        20% { transform: rotate(-10deg) scale(1.05); }
        30% { transform: rotate(8deg) scale(1.03); }
        40% { transform: rotate(-6deg) scale(1.03); }
        50% { transform: rotate(4deg) scale(1.02); }
        60% { transform: rotate(-2deg) scale(1.01); }
        70% { transform: rotate(1deg) scale(1.01); }
        80% { transform: rotate(0deg) scale(1); }
    }

    .notification-bell-shake .notification-bell-icon {
        animation: notification-shake 1s ease-in-out;
    }

    .notification-bell-shake {
        box-shadow: 0 4px 16px rgba(13, 110, 253, 0.3), 0 0 0 1px rgba(13, 110, 253, 0.2);
    }

    /* Urgent notification styling */
    .notification-urgent {
        animation: urgent-glow 2s infinite alternate;
    }

    @keyframes urgent-glow {
        0% {
            box-shadow: 0 0 5px rgba(220, 53, 69, 0.3);
            background: rgba(220, 53, 69, 0.05);
        }
        100% {
            box-shadow: 0 0 15px rgba(220, 53, 69, 0.5);
            background: rgba(220, 53, 69, 0.1);
        }
    }

    .notification-urgent .notification-bell-icon {
        color: #dc3545;
    }

    .notification-urgent:hover .notification-bell-icon {
        color: #b02a37;
    }

    /* Dropdown enhancements */
    .dropdown-menu {
        border: none;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.12);
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .notification-dropdown-header {
        background: linear-gradient(135deg, #ffffff 0%, #f1f8ff 100%);
        border-bottom: 2px solid #e3f2fd;
        position: relative;
    }

    .notification-dropdown-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #0d6efd, #6610f2, #0d6efd);
        background-size: 200% 100%;
        animation: header-shimmer 3s infinite;
    }

    @keyframes header-shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    .notification-dropdown-footer {
        background-color: #f8f9fa;
    }

    .notification-dropdown-footer .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        border-radius: 0.375rem;
        font-weight: 500;
    }

    /* Enhanced focus and active states */
    .notification-bell:focus {
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.3), 0 4px 12px rgba(13, 110, 253, 0.2);
        outline: none;
    }

    /* Active state for dropdown */
    .notification-bell[aria-expanded="true"] {
        background: rgba(13, 110, 253, 0.15);
        box-shadow: 0 4px 16px rgba(13, 110, 253, 0.25);
        transform: translateY(-1px);
    }

    .notification-bell[aria-expanded="true"] .notification-bell-icon {
        color: #0b5ed7;
        transform: rotate(15deg) scale(1.05);
        text-shadow: 0 2px 4px rgba(11, 94, 215, 0.4);
    }

    /* Enhanced visibility when notifications are present */
    .notification-bell:has(.notification-badge) {
        background: rgba(220, 53, 69, 0.05);
    }

    .notification-bell:has(.notification-badge):hover {
        background: rgba(220, 53, 69, 0.1);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
    }

    /* Loading state styling */
    .notification-loading {
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.5) 50%, transparent 70%);
        background-size: 200% 100%;
        animation: notification-shimmer 1.5s infinite;
    }

    .notification-spinner {
        position: relative;
    }

    @keyframes notification-shimmer {
        0% {
            background-position: -200% 0;
        }
        100% {
            background-position: 200% 0;
        }
    }




    /* Ensure no element causes overflow */
    .page-content {
        overflow-x: hidden;
    }

    /* Fix any Bootstrap components that might have oversized arrows */
    .dropdown-toggle::after,
    .dropup .dropdown-toggle::after,
    .dropend .dropdown-toggle::after,
    .dropstart .dropdown-toggle::before {
        display: inline-block;
        margin-left: 0.255em;
        vertical-align: 0.255em;
        content: "";
        border-top: 0.3em solid;
        border-right: 0.3em solid transparent;
        border-bottom: 0;
        border-left: 0.3em solid transparent;
        width: auto !important;
        height: auto !important;
        font-size: inherit !important;
    }



    /* Notification Dropdown Improvements */
    .dropdown-menu {
        max-width: 380px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .notification-item {
        transition: background-color 0.2s ease;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
    }

    .notification-icon .icon-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .notification-content {
        min-width: 0; /* Allows text-truncate to work in flex */
    }

    .notification-message {
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.4;
        color: #495057;
    }

    .notification-actions {
        display: flex;
        gap: 0.25rem;
    }

    .notification-actions .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 0.375rem;
    }

    /* Scrollable content area */
    #notificationList {
        max-height: 400px;
        overflow-y: auto;
    }

    /* Custom scrollbar */
    #notificationList::-webkit-scrollbar {
        width: 6px;
    }

    #notificationList::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    #notificationList::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    #notificationList::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .spin {
        animation: spin 1s linear infinite;
    }
    </style>
</body>
</html>
