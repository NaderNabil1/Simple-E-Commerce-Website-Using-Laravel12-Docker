<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    @include('Dashboard.head')
    <body>
        @include('Dashboard.sidebar')
        <main class="dashboard-main">
            @include('Dashboard.navbar')

            <div class="dashboard-main-body">
                @include('Dashboard.breadcrumb')
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <p class="alert alert-danger">{{ $error }}</p>
                        @endforeach
                @endif
                @yield('content')
            </div>
            @include('Dashboard.footer')
        </main>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        @include('Dashboard.scripts')
        @yield('javascripts')
    </body>
</html>
