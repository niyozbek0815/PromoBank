@extends('admin.layouts.app')

@section('title', 'Dashboard')
@push('scripts')
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/js/vendor/visualization/d3/d3.min.js"></script>
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/js/vendor/visualization/d3/d3_tooltip.js">
    </script>

    <script src="https://themes.kopyov.com/limitless/demo/template/assets/demo/pages/dashboard.js"></script>
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/demo/charts/pages/dashboard/streamgraph.js">
    </script>
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/demo/charts/pages/dashboard/sparklines.js">
    </script>
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/demo/charts/pages/dashboard/lines.js"></script>
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/demo/charts/pages/dashboard/areas.js"></script>
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/demo/charts/pages/dashboard/donuts.js"></script>
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/demo/charts/pages/dashboard/bars.js"></script>
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/demo/charts/pages/dashboard/progress.js"></script>
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/demo/charts/pages/dashboard/heatmaps.js"></script>
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/demo/charts/pages/dashboard/pies.js"></script>
    <script src="https://themes.kopyov.com/limitless/demo/template/assets/demo/charts/pages/dashboard/bullets.js"></script>
@endpush
@section('content')
    <div class="tab-content flex-1 order-2 order-lg-1">
        <div class="tab-pane fade show active" id="settings">

            <!-- Profile info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profile information</h5>
                </div>

                <div class="card-body">
                    <form action="#">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" value="Victoria" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Full name</label>
                                    <input type="text" value="Smith" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Address line 1</label>
                                    <input type="text" value="Ring street 12" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Address line 2</label>
                                    <input type="text" value="building D, flat #67" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" value="Munich" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">State/Province</label>
                                    <input type="text" value="Bayern" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">ZIP code</label>
                                    <input type="text" value="1031" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="text" readonly="readonly" value="victoria@smith.com"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Your country</label>
                                    <select class="form-select">
                                        <option value="germany" selected>Germany</option>
                                        <option value="france">France</option>
                                        <option value="spain">Spain</option>
                                        <option value="netherlands">Netherlands</option>
                                        <option value="other">...</option>
                                        <option value="uk">United Kingdom</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone #</label>
                                    <input type="text" value="+99-99-9999-9999" class="form-control">
                                    <div class="form-text text-muted">+99-99-9999-9999</div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Upload profile image</label>
                                    <input type="file" class="form-control">
                                    <div class="form-text text-muted">Accepted formats: gif, png, jpg.
                                        Max file size 2Mb</div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /profile info -->


            <!-- Account settings -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Account settings</h5>
                </div>

                <div class="card-body">
                    <form action="#">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" value="Vicky" readonly class="form-control">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Current password</label>
                                    <input type="password" value="password" readonly class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">New password</label>
                                    <input type="password" placeholder="Enter new password" class="form-control">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Repeat password</label>
                                    <input type="password" placeholder="Repeat new password" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Profile visibility</label>

                                    <label class="form-check mb-2">
                                        <input type="radio" name="visibility" class="form-check-input" checked>
                                        <span class="form-check-label">Visible to everyone</span>
                                    </label>

                                    <label class="form-check mb-2">
                                        <input type="radio" name="visibility" class="form-check-input">
                                        <span class="form-check-label">Visible to friends only</span>
                                    </label>

                                    <label class="form-check mb-2">
                                        <input type="radio" name="visibility" class="form-check-input">
                                        <span class="form-check-label">Visible to my connections
                                            only</span>
                                    </label>

                                    <label class="form-check">
                                        <input type="radio" name="visibility" class="form-check-input">
                                        <span class="form-check-label">Visible to my colleagues
                                            only</span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Notifications</label>

                                    <label class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" checked>
                                        <span class="form-check-label">Password expiration
                                            notification</span>
                                    </label>

                                    <label class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" checked>
                                        <span class="form-check-label">New message notification</span>
                                    </label>

                                    <label class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" checked>
                                        <span class="form-check-label">New task notification</span>
                                    </label>

                                    <label class="form-check">
                                        <input type="checkbox" class="form-check-input">
                                        <span class="form-check-label">New contact request
                                            notification</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /account settings -->

        </div>



</div>@endsection
