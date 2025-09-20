<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-15 mb-10">
    <h6 class="fw-semibold mb-0">@yield('breadcrumb_title')</h6>
    <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium">
            <a href="{{ url('/') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                Dashboard
            </a>
        </li>
        @yield('breadcrumbs')
        @if(Route::current()->getName() != 'dashboard')
        <li>-</li>
        <li class="fw-medium">@yield('breadcrumb_title')</li>
        @endif
    </ul>
</div>
