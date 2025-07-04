<!DOCTYPE html>
<html lang="zxx" class="js">
<head>
    <base href="../">
    <meta charset="utf-8">
    <meta name="author" content="zenerom">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="ZENEROM CREATIVE LAB TASK MANAGEMENT PORTAL">
    <!-- Fav Icon  -->
    <link rel="shortcut icon" href="./images/favicon.png">
    <!-- Page Title  -->
    <title>ZENEROM DASHBOARD</title>
    <!-- StyleSheets  -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script> -->

    <link rel="stylesheet" href="{{ asset('assets1/css/dashlite.css') }}">
    <link id="skin-default" rel="stylesheet" href="{{ asset('assets1/css/theme.css?ver=3.2.3') }}">
</head>

<body class="nk-body bg-lighter npc-general has-sidebar ">
    <div class="nk-app-root">
        <!-- main @s -->
        <div class="nk-main ">
            <!-- sidebar @s -->
            <div class="nk-sidebar nk-sidebar-fixed is-dark " data-content="sidebarMenu">
                <div class="nk-sidebar-element nk-sidebar-head">
                    <div class="nk-menu-trigger">
                        <a  class="nk-nav-toggle nk-quick-nav-icon d-xl-none" data-target="sidebarMenu"><em class="icon ni ni-arrow-left"></em></a>
                        <a  class="nk-nav-compact nk-quick-nav-icon d-none d-xl-inline-flex" data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
                    </div>
                    <div class="nk-sidebar-brand">
                                    <img class="logo-light logo-img" alt="logo" src="{{ asset('images/zenerom_logo.png') }}" alt="Logo" srcset="{{ asset('images/zenerom_logo.png 2x') }}">
                                    <img src="{{ asset('images/zenerom_logo.png') }}" srcset="{{ asset('images/zenerom_logo.png 2x') }}" alt="logo-dark" class="logo-dark logo-img">
                                </a>
                    </div>
                </div>
                    {{-- Include header --}}
                    @include('partials.header')

                    </div><!-- .nk-sidebar-content -->
                </div><!-- .nk-sidebar-element -->
            </div>
    {{-- Include menu --}}
    @include('partials.menu')


    {{-- Yield the content of the child view --}}
    @yield('content')


    {{-- Include footer --}}
    @include('partials.footer')

    <script src="{{ asset('assets/js/bundle.js?ver=3.2.3') }}"></script>
    <script src="{{ asset('assets/js/scripts.js?ver=3.2.3') }}"></script>
    <script src="{{ asset('assets/js/charts/gd-default.js?ver=3.2.3') }}"></script>
    <script src="{{ asset('assets/js/libs/datatable-btns.js?ver=3.2.3') }}"></script>

</body>

</html>
