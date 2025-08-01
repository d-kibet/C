<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ asset('backend/assets/images/Raha_logo.jpg') }}" alt="raha_logo" height="80">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('backend/assets/images/Raha_logo.jpg') }}" alt="raha_logo" height="140">
                    </span>
                </a>

                <a href="{{ route('dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ asset('backend/assets/images/Raha_logo.jpg') }}" alt="raha_logo" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('backend/assets/images/Raha_logo.jpg') }}" alt="raha_logo" height="40">
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
                <i class="ri-menu-2-line align-middle"></i>
            </button>

            <!-- App Search-->
            <form class="app-search d-none d-lg-block">
                <div class="position-relative">
                    <input type="text" class="form-control" placeholder="Search...">
                    <span class="ri-search-line"></span>
                </div>
            </form>


        </div>

        <div class="d-flex">





            <!-- Notifications -->
            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item notification-bell waves-effect" id="notificationDropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="notification-bell-container">
                        <i class="ri-notification-3-line notification-bell-icon"></i>
                        <span class="notification-badge" id="notificationBadge" style="display: none;"></span>
                        <div class="notification-pulse" id="notificationPulse" style="display: none;"></div>
                    </div>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="notificationDropdown" style="min-width: 380px; max-width: 420px;">
                    <div class="notification-dropdown-header p-3 bg-gradient">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0 text-dark fw-semibold">
                                    <i class="ri-notification-3-line me-2"></i>Notifications
                                </h6>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-external-link-line"></i> View All
                                </a>
                            </div>
                        </div>
                    </div>
                    <div data-simplebar style="max-height: 400px;" id="notificationList">
                        <div class="text-center p-4 notification-loading">
                            <div class="notification-spinner mb-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <h6 class="text-muted mb-1">Loading notifications</h6>
                            <p class="text-muted mb-0 small">Please wait a moment...</p>
                        </div>
                    </div>
                    <div class="notification-dropdown-footer p-3 border-top bg-light">
                        <div class="d-flex gap-2">
                            <a class="btn btn-sm btn-primary flex-fill" href="{{ route('notifications.index') }}">
                                <i class="ri-list-check-2"></i> All Notifications
                            </a>
                            <a class="btn btn-sm btn-warning" href="{{ route('notifications.overdue') }}">
                                <i class="ri-time-line"></i> Overdue
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dropdown d-none d-lg-inline-block ms-1">
                <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                    <i class="ri-fullscreen-line"></i>
                </button>
            </div>

            @php
                $id = Auth::user()->id;
            $adminData = App\Models\User::find($id);
            @endphp




            <div class="dropdown d-inline-block user-dropdown">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user"
                        src="{{ (!empty($adminData->profile_image))? url('upload/admin_images/'.$adminData->profile_image):url('upload/no_image.jpg') }}" alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1">{{ $adminData->name }}</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="ri-user-line align-middle me-1"></i> Profile</a>
                    <a class="dropdown-item" href="{{ route('change.password') }}"><i class="ri-wallet-2-line align-middle me-1"></i> Change Password</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="{{ route('admin.logout') }}"><i
                            class="ri-shut-down-line align-middle me-1 text-danger"></i> Logout</a>
                </div>
            </div>



        </div>
    </div>
</header>
