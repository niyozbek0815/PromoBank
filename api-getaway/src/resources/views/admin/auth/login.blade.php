<!DOCTYPE html>
<html lang="en" dir="ltr">

<!-- Mirrored from themes.kopyov.com/limitless/demo/template/html/layout_1/full/login_registration.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 24 Jun 2025 16:12:59 GMT -->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Limitless - Responsive Web Application Kit by Eugene Kopyov</title>

    <!-- Global stylesheets -->
    <link href="{{ asset('adminpanel/assets/css/inter.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('adminpanel/assets/css/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('adminpanel/assets/css/ltr/all.min.css') }}" id="stylesheet" rel="stylesheet" type="text/css">
    <link href="{{ asset('adminpanel/assets/phosphor-icons/phosphor-icons.css') }}" rel="stylesheet" type="text/css" />

    <!-- /global stylesheets -->
    <script src="{{ asset('adminpanel/assets/js/configurator.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/app.js') }}"></script>
    <script src="{{ asset('adminpanel/assets/js/bootstrap.min.js') }}"></script>

    <!-- Core JS files -->

    <!-- /core JS files -->

    <!-- Theme JS files -->
    <!-- /theme JS files -->

</head>

<body>

    <!-- Main navbar -->
    <div class="navbar navbar-dark navbar-static py-2">
        <div class="container-fluid">
            <div class="navbar-brand">
                <a href="index.html" class="d-inline-flex align-items-center">
                    <img src="{{ asset('adminpanel/assets/images/svg/logo_icon.svg') }}" alt="">
                    <img src="{{ asset('adminpanel/assets/images/svg/logo_text_light.svg') }}"
                        class="d-none d-sm-inline-block h-16px ms-3" alt="">
                </a>
            </div>

            <div class="d-flex justify-content-end align-items-center ms-auto">

            </div>
        </div>
    </div>
    <!-- /main navbar -->


    <!-- Page content -->
    <div class="page-content">

        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Inner content -->
            <div class="content-inner">

                <!-- Content area -->
                <div class="content d-flex justify-content-center align-items-center">

                    <!-- Login form -->
                    <form class="login-form" method="POST" action="{{ route('admin.login') }}">
                        @csrf
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <div class="d-inline-flex align-items-center justify-content-center mb-4 mt-2">
                                        <img src="{{ asset('adminpanel/assets/images/svg/logo_icon.svg') }}"
                                            class="h-48px" alt="">
                                    </div>
                                    <h5 class="mb-0">Login to your account</h5>
                                    <span class="d-block text-muted">Enter your credentials below</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <div class="form-control-feedback form-control-feedback-start">
                                        <input type="email" name="email" class="form-control"
                                            placeholder="john@doe.com" required>
                                        <div class="form-control-feedback-icon">
                                            <i class="ph-user-circle text-muted"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <div class="form-control-feedback form-control-feedback-start">
                                        <input type="password" name="password" class="form-control"
                                            placeholder="•••••••••••" required>
                                        <div class="form-control-feedback-icon">
                                            <i class="ph-lock text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                                @if ($errors->any())
                                    <div class="alert alert-danger mt-3">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary w-100">Sign in</button>
                                </div>

                                <div class="text-center">
                                    <a href="login_password_recover.html">Forgot password?</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- /login form -->

                </div>
                <!-- /content area -->


                <!-- Footer -->
                <div class="navbar navbar-sm navbar-footer border-top">
                    <div class="container-fluid">
                        <span>&copy; 2022 <a
                                href="https://themeforest.net/item/limitless-responsive-web-application-kit/13080328">Limitless
                                Web App Kit</a></span>

                    </div>
                </div>
                <!-- /footer -->

            </div>
            <!-- /inner content -->

        </div>
        <!-- /main content -->

    </div>
    <!-- /page content -->


    <!-- Demo config -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="demo_config">
        <div class="position-absolute top-50 end-100 visible">
            <button type="button" class="btn btn-primary btn-icon translate-middle-y rounded-end-0"
                data-bs-toggle="offcanvas" data-bs-target="#demo_config">
                <i class="ph-gear"></i>
            </button>
        </div>

        <div class="offcanvas-header border-bottom py-0">
            <h5 class="offcanvas-title py-3">Demo configuration</h5>
            <button type="button" class="btn btn-light btn-sm btn-icon border-transparent rounded-pill"
                data-bs-dismiss="offcanvas">
                <i class="ph-x"></i>
            </button>
        </div>
    </div>
    <!-- /demo config -->

</body>

<!-- Mirrored from themes.kopyov.com/limitless/demo/template/html/layout_1/full/login_registration.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 24 Jun 2025 16:12:59 GMT -->

</html>
