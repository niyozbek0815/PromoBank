<!DOCTYPE html>
<html lang="en">

<head>
    <title>@yield('title', 'Admin Panel')</title>
    @include('admin.layouts.header')
    @stack('scripts')
</head>

<body>
    @include('admin.layouts.navbar')

    <div class="page-content">
        @include('admin.layouts.sidebar')

        <div class="content-wrapper">
            <div class="content-inner">
                @include('admin.layouts.page-header')

                <div class="content">
                    @yield(section: 'content')
                </div>

                @include('admin.layouts.footer')
            </div>
        </div>
    </div>

    @include('admin.layouts.notifications')
    @include('admin.layouts.demo-config')
</body>

</html>
