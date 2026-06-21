<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Login') - DHS FINANCE</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            padding: 1rem;
        }

        .guest-wrapper {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        .guest-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-body {
            padding: 2.5rem 2rem !important;
        }

        .brand {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .brand img {
            max-height: 150px;
            width: auto;
            margin-bottom: 0.5rem;
            border-radius: 80px;
        }

        .form-label {
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-control {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.9375rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #cbd5e0;
        }

        .input-group .form-control {
            border-right: 0;
        }

        .input-group .btn {
            border-left: 0;
            background: transparent;
            border: 1.5px solid #e2e8f0;
            border-left: 0;
            color: #718096;
            padding: 0 1rem;
        }

        .input-group .btn:hover {
            background: #f7fafc;
            color: #667eea;
        }

        .btn-primary {
            background: #667eea;
            border: none;
            border-radius: 8px;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
            font-size: 0.9375rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .form-check-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 0.875rem 1rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .footer-text {
            text-align: center;
            color: #718096;
            font-size: 0.8125rem;
            margin-top: 1.5rem;
        }

        a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        a:hover {
            color: #5568d3;
        }

        .form-check-label {
            font-size: 0.875rem;
            color: #4a5568;
        }

        h3 {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .text-muted {
            color: #718096 !important;
        }

        .small {
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .card-body {
                padding: 2rem 1.5rem !important;
            }

            .brand img {
                max-height: 60px;
            }

            h3 {
                font-size: 1.5rem;
            }
        }

        /* Loading state */
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="guest-wrapper">
        <div class="guest-card">
            <div class="card-body">
                <div class="brand">
                    <a href="{{ url('/') }}">
                        <img src="/assets/img/dhs-logo.png" alt="DHS Finance">
                    </a>
                </div>

                @yield('content')
            </div>
        </div>
        
        <p class="footer-text">&copy; {{ date('Y') }} DHS FINANCE. All rights reserved.</p>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>