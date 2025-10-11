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
    @include('admin.components.dashboard')
@endsection
