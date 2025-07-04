
<div class="nk-wrap ">
                <!-- main header @s -->
                <div class="nk-header nk-header-fixed is-light">
                    <div class="container-fluid">
                        <div class="nk-header-wrap">
                            <div class="nk-menu-trigger d-xl-none ms-n1">
                                <a class="nk-nav-toggle nk-quick-nav-icon" data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
                            </div>
                            <div class="nk-header-brand d-xl-none">
                                <a class="logo-link">
                                    <img class="logo-light logo-img" alt="logo" src="{{ asset('images/logo.png') }}" alt="Logo" srcset="{{ asset('images/logo2x.png 2x') }}">
                                    <img src="{{ asset('images/logo-dark.png') }}" srcset="{{ asset('images/logo-dark2x.png 2x') }}" alt="logo-dark" class="logo-dark logo-img">
                                </a>
                            </div><!-- .nk-header-brand -->
                           
                            <div class="nk-header-tools">
                                <ul class="nk-quick-nav">
                                    
                                    <li class="dropdown user-dropdown">
                                        <a  class="dropdown-toggle" data-bs-toggle="dropdown">
                                            <div class="user-toggle">
                                                <div class="user-avatar bg-primary">
                                                    @if(session('image'))
                                                        <img src="{{ asset('uploads/employees/' . session('image')) }}" alt="{{ $uname }}" style="width:100%; height:100%; object-fit:cover;">
                                                    @else
                                                        <span>{{ strtoupper(substr($uname,0,1)) }}</span>
                                                    @endif
                                                </div>
                                                <div class="user-info d-none d-md-block">
                                                    <div class="user-status">{{ $uname }}</div>
                                                    <div class="user-name dropdown-indicator">{{ session('employee_id') }}</div>
                                                </div>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-md dropdown-menu-end dropdown-menu-s1">
                                            <div class="dropdown-inner user-card-wrap bg-lighter d-none d-md-block">
                                                <div class="user-card">
                                                    <!-- <div class="user-avatar">
                                                        <span>AB</span>
                                                    </div> -->
                                                    <div class="user-avatar bg-primary">
                                                        @if(session('image'))
                                                            <img src="{{ asset('uploads/employees/' . session('image')) }}" alt="{{ $uname }}" style="width:100%; height:100%; object-fit:cover;">
                                                        @else
                                                            <span>{{ strtoupper(substr($uname,0,1)) }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="user-info">
                                                        <span class="lead-text">{{ $uname }}</span>
                                                        <span class="sub-text">{{ session('role') }} | {{ session('department') }} </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dropdown-inner">
                                                <ul class="link-list">
                                                    <li><a href="html/user-profile-regular.html"><em class="icon ni ni-user-alt"></em><span>View Profile</span></a></li>
                                                    <li><a href="{{ route('reset.password') }}"><em class="icon ni ni-setting-alt"></em><span>Reset Password</span></a></li>
                                                    <li><a href="html/user-profile-activity.html"><em class="icon ni ni-activity-alt"></em><span>Login Activity</span></a></li>
                                                    <li><a class="dark-switch" ><em class="icon ni ni-moon"></em><span>Dark Mode</span></a></li>
                                                </ul>
                                            </div>
                                            <div class="dropdown-inner">
                                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                                    @csrf
                                                </form>
                                                <ul class="link-list">
                                                    <li><a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><em class="icon ni ni-signout"></em><span>Sign out</span></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li><!-- .dropdown -->
                                  @php
                                    // Retrieve unread notifications for the logged-in user
                                    $notifications = \App\Models\Notification::where('user_id', Auth::id())
                                                        ->whereNull('read_at')
                                                        ->orderBy('created_at', 'desc')
                                                        ->get();
                                @endphp

<li class="dropdown notification-dropdown me-n1">
    <a class="dropdown-toggle nk-quick-nav-icon" data-bs-toggle="dropdown">
        <div class="icon-status icon-status-info"><em class="icon ni ni-bell"></em></div>
    </a>
    <div class="dropdown-menu dropdown-menu-xl dropdown-menu-end dropdown-menu-s1">
        <div class="dropdown-head">
            <span class="sub-title nk-dropdown-title">Notifications</span>
        </div>
        <div class="dropdown-body">
            <div class="nk-notification">
                @if($notifications->count())
                    @foreach($notifications as $notification)
                        <div class="nk-notification-item dropdown-inner">
                            <div class="nk-notification-icon">
                                <em class="icon icon-circle bg-warning-dim ni ni-curve-down-right"></em>
                            </div>
                            <div class="nk-notification-content">
                                <div class="nk-notification-text">
                                    {{ $notification->title }}: {{ $notification->message }}
                                </div>
                                <div class="nk-notification-time">
                                    {{ $notification->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="nk-notification-item dropdown-inner">
                        <div class="nk-notification-content">
                            <div class="nk-notification-text">No new notifications</div>
                        </div>
                    </div>
                @endif
            </div><!-- .nk-notification -->
        </div><!-- .nk-dropdown-body -->
        <div class="dropdown-foot center">
            <a href="{{ route('notifications.all') }}">View All</a>
        </div>
    </div>
</li>
<script src="{{ asset('assets1/jquery.min.js') }}"></script>
<script>
// $(document).ready(function(){
//     // When the notifications dropdown is opened...
//     $('.notification-dropdown').on('show.bs.dropdown', function(){
//         $.ajax({
//             url: '{{ route("notifications.markAsRead") }}',
//             type: 'POST',
//             data: {
//                 _token: '{{ csrf_token() }}'
//             },
//             success: function(response){
//                 console.log('Notifications marked as read:', response);
//                 // Optionally, update the UI to remove notification count, etc.
//             },
//             error: function(xhr){
//                 console.log('Error marking notifications as read:', xhr.responseText);
//             }
//         });
//     });
// });
</script>



                                    <li class="dropdown notification-dropdown me-n1">
                                        <a  class="dropdown-toggle nk-quick-nav-icon" data-bs-toggle="dropdown">
                                            <div class="icon-status icon-status-info"><em class="icon ni ni-mail"></em></div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-xl dropdown-menu-end dropdown-menu-s1">
                                            <div class="dropdown-head">
                                                <span class="sub-title nk-dropdown-title">Mails</span>
                                            </div>
                                            <div class="dropdown-body">
                                                <div class="nk-notification">
                                                    

                                                    <div class="nk-notification-item dropdown-inner">
                                                        <div class="nk-notification-icon">
                                                            <em class="icon icon-circle bg-warning-dim ni ni-curve-down-right"></em>
                                                        </div>
                                                        <div class="nk-notification-content">
                                                            <div class="nk-notification-text">You have requested to <span>Widthdrawl</span></div>
                                                            <div class="nk-notification-time">2 hrs ago</div>
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    
                                                    
                                                    
                                                </div><!-- .nk-notification -->
                                            </div><!-- .nk-dropdown-body -->
                                            <div class="dropdown-foot center">
                                                <a >View All</a>
                                            </div>
                                        </div>
                                    </li><!-- .dropdown -->

                                </ul><!-- .nk-quick-nav -->
                            </div><!-- .nk-header-tools -->
                        </div><!-- .nk-header-wrap -->
                    </div><!-- .container-fliud -->
                </div>
                
