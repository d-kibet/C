<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!-- User details -->


        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title">Menu</li>

                <li>
                    <a href="{{ route('dashboard') }}" class="waves-effect">
                        <i class="ri-dashboard-line"></i><span class="badge rounded-pill bg-success float-end"></span>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if(Auth::user()->can('carpet.menu'))
                <li>
                    <a href="javascript: void(0);" data-bs-toggle="collapse">
                        <i class="mdi mdi-layers-outline"></i>
                        <span>Carpets</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">

                        @if(Auth::user()->can('carpet.all'))
                        <li><a href="{{ route('all.carpet') }}">All Carpet Records</a></li>
                        @endif

                        @if(Auth::user()->can('carpet.add'))
                        <li><a href="{{ route('add.carpet') }}">Add Carpet Records</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(Auth::user()->can('laundry.menu'))
                <li>
                    <a href="javascript: void(0);" data-bs-toggle="collapse">
                        <i class="fa-solid fa-shirt"></i>
                        <span>Laundry</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">

                        @if(Auth::user()->can('laundry.all'))
                        <li><a href="{{ route('all.laundry') }}">All Laundry Records</a></li>
                        @endif

                        @if(Auth::user()->can('laundry.add'))
                        <li><a href="{{ route('add.laundry') }}">Add Laundry Record</a></li>
                        @endif

                    </ul>
                </li>
                @endif

                @if(Auth::user()->can('mpesa.menu'))
                <li>
                    <a href="javascript: void(0);" data-bs-toggle="collapse">
                        <i class="fa-solid fa-sack-dollar"></i>
                        <span>M-pesa</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">

                        @if(Auth::user()->can('mpesa.all'))
                        <li><a href="{{ route('all.mpesa') }}">All M-pesa Records</a></li>
                        @endif

                        @if(Auth::user()->can('mpesa.add'))
                        <li><a href="{{ route('add.mpesa') }}">Add M-pesa Record</a></li>
                        @endif


                    </ul>
                </li>
                @endif

                @if(Auth::user()->can('mpesa.compare'))
                <li>
                    <a href="javascript: void(0);" data-bs-toggle="collapse">
                        <i class="fa-regular fa-clipboard"></i>
                        <span>Reports</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">

                        @if(Auth::user()->can('mpesa.compare'))
                        <li><a href="{{ route('reports.carpets.today') }}">Carpet's Report</a></li>
                        @endif

                        @if(Auth::user()->can('mpesa.compare'))
                        <li><a href="{{ route('reports.laundry.today') }}">Laundry Report</a></li>
                        @endif

                        @if(Auth::user()->can('mpesa.compare'))
                        <li><a href="{{ route('reports.mpesa.today') }}">M-pesa Report</a></li>
                        @endif

                        @if(Auth::user()->can('mpesa.compare'))
                        <li><a href="{{ route('reports.specific_report') }}">Specific Report</a></li>
                        @endif

                        @if(Auth::user()->can('admin.all'))
                        <li><a href="{{ route('reports.performance') }}">Performance Dashboard</a></li>
                        @endif

                        @if(Auth::user()->can('admin.all'))
                        <li><a href="{{ route('customer.retention.index') }}">Customer Retention</a></li>
                        @endif


                    </ul>
                </li>
                @endif

                @if(Auth::user()->can('mpesa.menu'))
                <li>
                    <a href="javascript: void(0);" data-bs-toggle="collapse">
                        <i class="fas fa-receipt"></i>
                        <span>Expenses</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">

                        @if(Auth::user()->can('mpesa.all'))
                        <li><a href="{{ route('expenses.index') }}">All Expenses</a></li>
                        @endif

                        @if(Auth::user()->can('mpesa.add'))
                        <li><a href="{{ route('expenses.create') }}">Add Expense</a></li>
                        @endif

                        @if(Auth::user()->can('mpesa.compare'))
                        <li><a href="{{ route('expenses.dashboard') }}">Expense Dashboard</a></li>
                        @endif

                    </ul>
                </li>
                @endif

                <!-- SMS Management -->
                @if(Auth::user()->can('admin.all'))
                <li>
                    <a href="javascript: void(0);" data-bs-toggle="collapse">
                        <i class="ri-message-2-line"></i>
                        <span>SMS</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('sms.dashboard') }}">
                            <i class="ri-dashboard-3-line me-2"></i>SMS Dashboard
                        </a></li>

                        <li><a href="{{ route('sms.send') }}">
                            <i class="ri-send-plane-2-line me-2"></i>Send Single SMS
                        </a></li>

                        <li><a href="{{ route('sms.bulk') }}">
                            <i class="ri-send-plane-fill me-2"></i>Send Bulk SMS
                        </a></li>

                        <li><a href="{{ route('sms.history') }}">
                            <i class="ri-history-line me-2"></i>SMS History
                        </a></li>

                        <li><a href="{{ route('sms.statistics') }}">
                            <i class="ri-bar-chart-line me-2"></i>SMS Statistics
                        </a></li>
                    </ul>
                </li>
                @endif

                <!-- System Management -->
                <li>
                    <a href="javascript: void(0);" data-bs-toggle="collapse">
                        <i class="ri-settings-4-line"></i>
                        <span>Follow-Ups</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('notifications.index') }}">
                            <i class="ri-notification-3-line me-2"></i>Notifications
                        </a></li>

                        @if(Auth::user()->can('admin.all'))
                        <li><a href="{{ route('audit.index') }}">
                            <i class="ri-file-list-3-line me-2"></i>Audit Trail
                        </a></li>
                        @endif

                        <li><a href="{{ route('notifications.overdue') }}">
                            <i class="ri-time-line me-2"></i>Overdue Alerts
                        </a></li>
                    </ul>
                </li>

                @if(Auth::user()->can('roles.menu'))
                <li>
                    <a href="#permission" data-bs-toggle="collapse">
                        <i class="mdi mdi-email-multiple-outline"></i>
                        <span> Roles And Permission    </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="permission">
                        <ul class="nav-second-level">
                            @if(Auth::user()->can('permission.all'))
                            <li>
                                <a href="{{ route('all.permission') }}">All Permission </a>
                            </li>
                            @endif

                            @if(Auth::user()->can('roles.all'))
                            <li>
                                <a href="{{ route('all.roles') }}">All Roles </a>
                            </li>
                            @endif

                            @if(Auth::user()->can('role.permission'))
                            <li>
                                <a href="{{ route('add.roles.permission') }}">Roles in Permission </a>
                            </li>
                            @endif

                            @if(Auth::user()->can('all.roles.permission'))
                            <li>
                                <a href="{{ route('all.roles.permission') }}">All Roles in Permission </a>
                            </li>
                            @endif

                        </ul>
                    </div>
                </li>
                @endif

                @if(Auth::user()->can('admin.user.menu'))
                <li>
                    <a href="#admin" data-bs-toggle="collapse">
                        <i class="mdi mdi-email-multiple-outline"></i>
                        <span> Setting Admin User    </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="admin">
                        <ul class="nav-second-level">

                            @if(Auth::user()->can('admin.all'))
                            <li>
                                <a href="{{ route('all.admin') }}">All Admin </a>
                            </li>
                            @endif

                            @if(Auth::user()->can('admin.add'))
                            <li>
                                <a href="{{ route('add.admin') }}">Add Admin </a>
                            </li>
                            @endif

                        </ul>
                    </div>
                </li>
                @endif

                @if(Auth::user()->can('database.menu'))
                <li>
                    <a href="javascript: void(0);" data-bs-toggle="collapse">
                        <i class="fa-solid fa-database"></i>
                        <span>Database Backup</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">

                        @if(Auth::user()->can('database.all'))
                        <li><a href="{{ route('database.backup') }}">Database Backup</a></li>
                        @endif


                    </ul>
                </li>
                @endif







            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
