<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>
    @include('admin.layouts.header')
    @stack('scripts')
</head>

<body>
    @php
        $user = Session::get('user');
    @endphp
    @include('admin.layouts.navbar')

    <div class="page-content">
        @include('admin.layouts.sidebar')

        <div class="content-wrapper">
            <div class="content-inner">
                @include('admin.layouts.page-header')

                <div class="content">
                    @yield('content')
                </div>

                @include('admin.layouts.footer')
            </div>
        </div>
    </div>

    @include('admin.layouts.notifications')
    @include('admin.layouts.demo-config')
    @if (session('error'))
        <script>
            $(function() {
                toastr.error(@json(session('error')));
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            $(function() {
                @foreach ($errors->all() as $error)
                    toastr.error(@json($error));
                @endforeach
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            $(function() {
                toastr.success(@json(session('success')));
            });
        </script>
    @endif
</body>

</html>
