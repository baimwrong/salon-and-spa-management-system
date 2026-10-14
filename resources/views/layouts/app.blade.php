<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Salon & Spa Management System') }}</title>

    <!-- Local Offline Assets - 100% Internal Network Ready -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/luxury-theme.css') }}">

    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar Navigation -->
        <aside class="aura-sidebar d-flex flex-column flex-shrink-0 text-white">
            <div class="aura-brand d-flex align-items-center gap-2">
                <div class="aura-stat-icon aura-icon-gold" style="width: 36px; height: 36px; font-size: 1.1rem;">
                    <i class="bi bi-flower1"></i>
                </div>
                <div>
                    <div class="aura-brand-title">SALON & <span class="aura-brand-gold">SPA</span></div>
                    <div class="aura-brand-subtitle">Management System</div>
                </div>
            </div>

            <div class="flex-grow-1 overflow-auto py-3">
                <div class="aura-nav-header">Main Operations</div>
                <a href="{{ route('dashboard') }}" class="aura-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('appointments.index') }}" class="aura-nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i>
                    <span>Appointments</span>
                </a>
                <a href="{{ route('pos.terminal') }}" class="aura-nav-link {{ request()->routeIs('pos.terminal') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i>
                    <span>Front Desk POS</span>
                </a>
                <a href="{{ route('pos.invoices.index') }}" class="aura-nav-link {{ request()->routeIs('pos.invoices.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>Invoices & Billing</span>
                </a>
                <a href="{{ route('customers.index') }}" class="aura-nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Customer CRM</span>
                </a>

                @if(auth()->user()->isManager())
                    <div class="aura-nav-header mt-2">Management</div>
                    <a href="{{ route('services.index') }}" class="aura-nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
                        <i class="bi bi-scissors"></i>
                        <span>Services & Menu</span>
                    </a>
                    <a href="{{ route('products.index') }}" class="aura-nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i>
                        <span>Inventory & Retail</span>
                    </a>
                    <a href="{{ route('staff.index') }}" class="aura-nav-link {{ request()->routeIs('staff.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i>
                        <span>Staff & Stylists</span>
                    </a>
                    <a href="{{ route('commissions.index') }}" class="aura-nav-link {{ request()->routeIs('commissions.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Commission Ledger</span>
                    </a>
                @endif

                @if(auth()->user()->isAdmin())
                    <div class="aura-nav-header mt-2">Administration</div>
                    <a href="{{ route('admin.import.index') }}" class="aura-nav-link {{ request()->routeIs('admin.import.*') ? 'active' : '' }}">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span>Bulk Data Import</span>
                    </a>
                @endif
            </div>

            <!-- User Footer in Sidebar -->
            <div class="p-3 border-top border-secondary border-opacity-25 bg-black bg-opacity-25">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold" style="width: 34px; height: 34px; font-size: 0.85rem;">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="text-truncate">
                            <div class="fw-semibold text-white small text-truncate">{{ auth()->user()->name }}</div>
                            <div class="text-muted" style="font-size: 0.72rem; text-transform: capitalize;">{{ auth()->user()->role }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-link text-muted p-0 text-decoration-none" title="Log Out">
                            <i class="bi bi-box-arrow-right fs-5"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-grow-1 d-flex flex-column min-vh-100 overflow-hidden">
            <!-- Topbar -->
            <header class="aura-topbar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'Management Portal')</h5>
                    @yield('page_subtitle')
                </div>

                <div class="d-flex align-items-center gap-3">
                    <!-- Quick Demo Role Switcher -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-switch"></i>
                            <span>Switch Role (Demo)</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><h6 class="dropdown-header">Switch Active Role</h6></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('quick.login', 'admin') }}"><span class="badge bg-danger">Admin</span> Victoria Sterling</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('quick.login', 'manager') }}"><span class="badge bg-primary">Manager</span> Alexander Hayes</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('quick.login', 'receptionist') }}"><span class="badge bg-success">Receptionist</span> Grace Montgomery</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('quick.login', 'therapist') }}"><span class="badge bg-warning text-dark">Therapist</span> Elena Vance</a></li>
                        </ul>
                    </div>

                    <!-- Quick POS action -->
                    <a href="{{ route('pos.terminal') }}" class="btn btn-sm btn-aura-gold d-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-plus-circle"></i>
                        <span>New Sale</span>
                    </a>

                    <!-- User Avatar -->
                    <div class="dropdown">
                        <button class="btn btn-light rounded-circle p-1 border shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5 text-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold">{{ auth()->user()->name }}</div>
                                <div class="small text-muted">{{ auth()->user()->email }}</div>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('staff.show', auth()->user()) }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="flex-grow-1 p-4 overflow-auto">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>{{ session('warning') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                        <i class="bi bi-x-octagon-fill fs-5"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>{{ session('info') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-exclamation-circle-fill fs-5"></i>
                            <strong>Please check the following form errors:</strong>
                        </div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Local Offline JS Assets -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>

    @stack('scripts')
</body>
</html>
