<!-- .nk-sidebar-element -->
                <div class="nk-sidebar-element nk-sidebar-body">
                    <div class="nk-sidebar-content">
                        <div class="nk-sidebar-menu" data-simplebar>
                            <ul class="nk-menu">
                <li class="nk-menu-heading">
                </li>
               @php
                    $user = Auth::user();
                    // Determine dashboard route based on user role.
                    switch($user->role_id) {
                        case 1:
                            $dashboardRoute = route('dashboard.index');
                            break;
                        case 2:
                            $dashboardRoute = route('dashboard.techhead');
                            break;
                        case 3:
                            $dashboardRoute = route('dashboard.teamlead');
                            break;
                        case 4:
                            $dashboardRoute = route('dashboard.staff');
                            break;
                        case 5:
                            $dashboardRoute = route('dashboard.projectmanager');
                            break;
                        case 6:
                            $dashboardRoute = route('dashboard.interns');
                            break;
                        case 7:
                            $dashboardRoute = route('dashboard.hr');
                            break;
                        default:
                            $dashboardRoute = route('dashboard.nocontent');
                            break;
                    }
                @endphp

                {{-- Dashboard (menu_item id: 1) --}}
                @if(session()->has('user_privileges') && session('user_privileges')->has(1) && session('user_privileges')->get(1)->can_view)
                <li class="nk-menu-item">
                    <a href="{{ $dashboardRoute }}" class="nk-menu-link">
                        <span class="nk-menu-icon"><em class="icon ni ni-grid-fill-c"></em></span>
                        <span class="nk-menu-text">Dashboard</span>
                    </a>
                </li>
                @endif

                {{-- Tasks (menu_item id: 2) --}}
                @if(session()->has('user_privileges') && session('user_privileges')->has(2) && session('user_privileges')->get(2)->can_view)
                <li class="nk-menu-item has-sub">
                    <a href="javascript:void(0)" class="nk-menu-link nk-menu-toggle">
                        <span class="nk-menu-icon"><em class="icon ni ni-list-check"></em></span>
                        <span class="nk-menu-text">Tasks</span>
                    </a>
                    <ul class="nk-menu-sub">
                        {{-- My Tasks (menu_item id: 8) --}}
                        @if(session('user_privileges')->has(8) && session('user_privileges')->get(8)->can_view)
                        <li class="nk-menu-item">
                            <a href="{{ route('my_tasks.index') }}" class="nk-menu-link">
                                <span class="nk-menu-text">My Tasks</span>
                            </a>
                        </li>
                        @endif
                        {{-- All Tasks (menu_item id: 10) --}}
                        @if(session('user_privileges')->has(10) && session('user_privileges')->get(10)->can_view)
                        <li class="nk-menu-item">
                            <a href="{{ route('tasks.index') }}" class="nk-menu-link">
                                <span class="nk-menu-text">All Tasks</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                {{-- Projects (menu_item id: 3) --}}
                @if(session()->has('user_privileges') && session('user_privileges')->has(3) && session('user_privileges')->get(3)->can_view)
                <li class="nk-menu-item has-sub">
                    <a href="javascript:void(0)" class="nk-menu-link nk-menu-toggle">
                        <span class="nk-menu-icon"><em class="icon ni ni-laptop"></em></span>
                        <span class="nk-menu-text">Projects</span>
                    </a>
                    <ul class="nk-menu-sub">
                        {{-- Clients (menu_item id: 11) --}}
                        @if(session('user_privileges')->has(11) && session('user_privileges')->get(11)->can_view)
                        <li class="nk-menu-item">
                            <a href="{{ route('customer.index') }}" class="nk-menu-link">
                                <span class="nk-menu-text">Clients</span>
                            </a>
                        </li>
                        @endif
                        {{-- Services (menu_item id: 12) --}}
                        @if(session('user_privileges')->has(12) && session('user_privileges')->get(12)->can_view)
                        <li class="nk-menu-item">
                            <a href="{{ route('service.index') }}" class="nk-menu-link">
                                <span class="nk-menu-text">Services</span>
                            </a>
                        </li>
                        @endif
                        {{-- Projects (menu_item id: 13) --}}
                        @if(session('user_privileges')->has(13) && session('user_privileges')->get(13)->can_view)
                        <li class="nk-menu-item">
                            <a href="{{ route('project.index') }}" class="nk-menu-link">
                                <span class="nk-menu-text">Projects</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                {{-- Staffs (menu_item id: 4) --}}
                @if(session()->has('user_privileges') && session('user_privileges')->has(4) && session('user_privileges')->get(4)->can_view)
                <li class="nk-menu-item has-sub">
                    <a href="javascript:void(0)" class="nk-menu-link nk-menu-toggle">
                        <span class="nk-menu-icon"><em class="icon ni ni-user-list"></em></span>
                        <span class="nk-menu-text">Staffs</span>
                    </a>
                    <ul class="nk-menu-sub">
                        {{-- Attendance (menu_item id: 14) --}}
                        @if(session('user_privileges')->has(14) && session('user_privileges')->get(14)->can_view)
                        <li class="nk-menu-item">
                            <a href="{{ route('attendance.index') }}" class="nk-menu-link">
                                <span class="nk-menu-text">Attendance</span>
                            </a>
                        </li>
                        @endif
                        {{-- Profile (menu_item id: 15) --}}
                        @if(session('user_privileges')->has(15) && session('user_privileges')->get(15)->can_view)
                        <li class="nk-menu-item">
                            <a href="javascript:void(0)" class="nk-menu-link">
                                <span class="nk-menu-text">Profile</span>
                            </a>
                        </li>
                        @endif
                        {{-- Leave Requests (menu_item id: 16) --}}
                        @if(session()->has('user_privileges') && session('user_privileges')->has(16) && session('user_privileges')->get(16)->can_view)
                        <li class="nk-menu-item">
                            <a href="{{ route('leave_requests.index') }}" class="nk-menu-link">
                                <span class="nk-menu-text">Leave Requests</span>
                            </a>
                        </li>
                        @endif
                        {{-- Salary (menu_item id: 17) --}}
                        @if(session('user_privileges')->has(17) && session('user_privileges')->get(17)->can_view)
                        <li class="nk-menu-item">
                            <a href="javascript:void(0)" class="nk-menu-link">
                                <span class="nk-menu-text">Salary</span>
                            </a>
                        </li>
                        @endif
                        {{-- Performance (menu_item id: 18) --}}
                        @if(session('user_privileges')->has(18) && session('user_privileges')->get(18)->can_view)
                        <li class="nk-menu-item">
                            <a href="javascript:void(0)" class="nk-menu-link">
                                <span class="nk-menu-text">Performance</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                {{-- Calendar (menu_item id: 5) --}}
                @if(session()->has('user_privileges') && session('user_privileges')->has(5) && session('user_privileges')->get(5)->can_view)
                <li class="nk-menu-item">
                    <a href="javascript:void(0)" class="nk-menu-link">
                        <span class="nk-menu-icon"><em class="icon ni ni-calendar-booking"></em></span>
                        <span class="nk-menu-text">Calendar</span>
                    </a>
                </li>
                @endif

                {{-- Accounts (menu_item id: 6) --}}
                @if(session()->has('user_privileges') && session('user_privileges')->has(6) && session('user_privileges')->get(6)->can_view)
                <li class="nk-menu-item has-sub">
                    <a href="javascript:void(0)" class="nk-menu-link nk-menu-toggle">
                        <span class="nk-menu-icon"><em class="icon ni ni-coins"></em></span>
                        <span class="nk-menu-text">Accounts</span>
                    </a>
                    <ul class="nk-menu-sub">
                        {{-- Income (menu_item id: 19) --}}
                        @if(session('user_privileges')->has(19) && session('user_privileges')->get(19)->can_view)
                        <li class="nk-menu-item">
                            <a href="javascript:void(0)" class="nk-menu-link">
                                <span class="nk-menu-text">Income</span>
                            </a>
                        </li>
                        @endif
                        {{-- Expense (menu_item id: 20) --}}
                        @if(session('user_privileges')->has(20) && session('user_privileges')->get(20)->can_view)
                        <li class="nk-menu-item">
                            <a href="javascript:void(0)" class="nk-menu-link">
                                <span class="nk-menu-text">Expense</span>
                            </a>
                        </li>
                        @endif
                        {{-- Invoice (menu_item id: 21) --}}
                        @if(session('user_privileges')->has(21) && session('user_privileges')->get(21)->can_view)
                        <li class="nk-menu-item">
                            <a href="javascript:void(0)" class="nk-menu-link">
                                <span class="nk-menu-text">Invoice</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                {{-- Settings (menu_item id: 7) --}}
                @if(session()->has('user_privileges') && session('user_privileges')->has(7) && session('user_privileges')->get(7)->can_view)
                <li class="nk-menu-item has-sub">
                    <a href="javascript:void(0)" class="nk-menu-link nk-menu-toggle">
                        <span class="nk-menu-icon"><em class="icon ni ni-setting-alt"></em></span>
                        <span class="nk-menu-text">Settings</span>
                    </a>
                    <ul class="nk-menu-sub">
                        {{-- Departments (menu_item id: 22) --}}
                        @if(session('user_privileges')->has(22) && session('user_privileges')->get(22)->can_view)
                        <li class="nk-menu-item">
                            <a href="{{ route('department.index') }}" class="nk-menu-link">
                                <span class="nk-menu-text">Departments</span>
                            </a>
                        </li>
                        @endif
                        {{-- Staffs (menu_item id: 23) --}}
                        @if(session('user_privileges')->has(23) && session('user_privileges')->get(23)->can_view)
                        <li class="nk-menu-item">
                            <a href="{{ route('employee.index') }}" class="nk-menu-link">
                                <span class="nk-menu-text">Staffs</span>
                            </a>
                        </li>
                        @endif
                        {{-- Roles (menu_item id: 24) --}}
                        @if(session('user_privileges')->has(24) && session('user_privileges')->get(24)->can_view)
                        <li class="nk-menu-item">
                            <a href="{{ route('role.index') }}" class="nk-menu-link">
                                <span class="nk-menu-text">Roles</span>
                            </a>
                        </li>
                        @endif
                        {{-- Reset Password (menu_item id: 26) --}}
                        @if(session('user_privileges')->has(26) && session('user_privileges')->get(26)->can_view)
                        <li class="nk-menu-item">
                            <a href="{{ route('reset.password') }}" class="nk-menu-link">
                                <span class="nk-menu-text">Reset Password</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
            </ul>
                        </div><!-- .nk-sidebar-menu -->
