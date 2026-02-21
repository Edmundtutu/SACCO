<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Select SACCO - Admin Panel</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #3399CC;
            --primary-dark: #2980b9;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .page-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .page-header {
            background: var(--primary-color);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .page-body {
            padding: 2rem;
        }

        .sacco-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.1s ease;
            cursor: pointer;
        }

        .sacco-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 16px rgba(51, 153, 204, 0.2);
            transform: translateY(-2px);
        }

        .sacco-logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 8px;
        }

        .sacco-logo-placeholder {
            width: 56px;
            height: 56px;
            background: #f0f7ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: var(--primary-color);
        }

        .btn-enter {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .btn-enter:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            color: white;
        }

        .role-badge {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .back-link {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <div class="page-card">
                    <div class="page-header">
                        <div class="mb-2" style="font-size: 2.5rem;">
                            <i class="bi bi-building"></i>
                        </div>
                        <h4 class="mb-1">Select Your SACCO</h4>
                        <p class="mb-0 opacity-75" style="font-size: 0.9rem;">
                            Your account is linked to multiple SACCOs. Choose which one to manage.
                        </p>
                    </div>

                    <div class="page-body">

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="d-flex flex-column gap-3">
                            @foreach($candidates as $candidate)
                                <form method="POST" action="{{ route('admin.select-sacco.submit') }}">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $candidate['user_id'] }}">

                                    <button type="submit" class="sacco-card w-100 p-3 border-0 bg-white text-start">
                                        <div class="d-flex align-items-center gap-3">

                                            {{-- Logo or placeholder --}}
                                            <div class="flex-shrink-0">
                                                @if(!empty($candidate['logo_url']))
                                                    <img src="{{ $candidate['logo_url'] }}"
                                                         alt="{{ $candidate['sacco_name'] }}"
                                                         class="sacco-logo">
                                                @else
                                                    <div class="sacco-logo-placeholder">
                                                        <i class="bi bi-bank"></i>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- SACCO info --}}
                                            <div class="flex-grow-1 min-width-0">
                                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                    <span class="fw-semibold text-dark">
                                                        {{ $candidate['sacco_name'] }}
                                                    </span>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary role-badge">
                                                        {{ $candidate['sacco_code'] }}
                                                    </span>
                                                </div>

                                                <div class="d-flex gap-2 flex-wrap">
                                                    {{-- Status badge --}}
                                                    @if($candidate['status'] === 'active')
                                                        <span class="badge bg-success bg-opacity-15 text-success role-badge">
                                                            <i class="bi bi-check-circle me-1"></i>Active
                                                        </span>
                                                    @elseif($candidate['status'] === 'suspended')
                                                        <span class="badge bg-warning bg-opacity-15 text-warning role-badge">
                                                            <i class="bi bi-pause-circle me-1"></i>Suspended
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary bg-opacity-15 text-secondary role-badge">
                                                            {{ ucfirst($candidate['status']) }}
                                                        </span>
                                                    @endif

                                                    {{-- Role badge --}}
                                                    @php
                                                        $roleColors = [
                                                            'super_admin'   => 'danger',
                                                            'admin'         => 'primary',
                                                            'staff_level_1' => 'info',
                                                            'staff_level_2' => 'info',
                                                            'staff_level_3' => 'info',
                                                        ];
                                                        $roleColor = $roleColors[$candidate['role']] ?? 'secondary';
                                                        $roleLabel = str_replace('_', ' ', ucfirst($candidate['role']));
                                                    @endphp
                                                    <span class="badge bg-{{ $roleColor }} bg-opacity-15 text-{{ $roleColor }} role-badge">
                                                        {{ $roleLabel }}
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- Arrow --}}
                                            <div class="flex-shrink-0 text-muted">
                                                <i class="bi bi-arrow-right-circle fs-5"></i>
                                            </div>
                                        </div>
                                    </button>
                                </form>
                            @endforeach
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <a href="{{ route('admin.logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                               class="text-muted text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>Back / Sign in as different user
                            </a>
                            <form id="logout-form" method="POST" action="{{ route('admin.logout') }}" class="d-none">
                                @csrf
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
