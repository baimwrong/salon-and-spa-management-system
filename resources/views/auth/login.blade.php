<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'Salon & Spa Management System') }}</title>

    <!-- Local Offline Assets -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/luxury-theme.css') }}">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="container" style="max-width: 460px;">
        <!-- Logo & Header -->
        <div class="text-center mb-4">
            <div class="d-inline-flex p-3 rounded-circle bg-dark text-warning mb-3 shadow">
                <i class="bi bi-flower1 fs-1"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">SALON & SPA</h3>
            <p class="text-muted small text-uppercase" style="letter-spacing: 2px;">Management System</p>
        </div>

        <!-- Flash alerts -->
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-1"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Card Login Form -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <div class="card-body p-4 p-md-5">
                <h5 class="fw-bold mb-4 text-center">Sign In to Workspace</h5>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control border-start-0 @error('email') is-invalid @enderror" value="{{ old('email', 'admin@salonspa.local') }}" required autofocus placeholder="admin@salonspa.local">
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control border-start-0 @error('password') is-invalid @enderror" required placeholder="••••••••" value="password">
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                            <label class="form-check-label small text-muted" for="remember">Keep me signed in</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-aura-primary w-100 py-2 fw-semibold">
                        Sign In
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick 1-Click Demo Accounts -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3">
                <div class="text-center small text-muted fw-semibold mb-2 text-uppercase" style="letter-spacing: 1px;">
                    1-Click Instant Demo Login
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('quick.login', 'admin') }}" class="btn btn-sm btn-outline-danger w-100 text-truncate">
                            <i class="bi bi-shield-lock me-1"></i> Admin
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('quick.login', 'manager') }}" class="btn btn-sm btn-outline-primary w-100 text-truncate">
                            <i class="bi bi-briefcase me-1"></i> Manager
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('quick.login', 'receptionist') }}" class="btn btn-sm btn-outline-success w-100 text-truncate">
                            <i class="bi bi-headset me-1"></i> Front Desk
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('quick.login', 'therapist') }}" class="btn btn-sm btn-outline-warning text-dark w-100 text-truncate">
                            <i class="bi bi-scissors me-1"></i> Stylist
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 text-muted small">
            &copy; {{ date('Y') }} Salon & Spa Management System. Offline-Ready Enterprise System.
        </div>
    </div>

    <!-- Local Offline JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
