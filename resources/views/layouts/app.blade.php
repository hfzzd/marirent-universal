<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MariRent Universal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .hero-gradient { background: linear-gradient(135deg, #1e3a5f 0%, #0d9488 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .category-btn.active { background-color: #0d9488; color: white; }
        .price-badge { background: linear-gradient(135deg, #0d9488, #14b8a6); }
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover, .sidebar-link.active { background-color: #f0fdfa; color: #0d9488; border-right: 3px solid #0d9488; }
        .stat-card { background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%); }
        .sidebar { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); }
        .btn-primary { background: linear-gradient(135deg, #0d9488, #14b8a6); }
        .btn-primary:hover { background: linear-gradient(135deg, #0f766e, #0d9488); }
        .status-available { background-color: #dcfce7; color: #166534; }
        .status-rented { background-color: #fef3c7; color: #92400e; }
        .status-maintenance { background-color: #fee2e2; color: #991b1b; }
        .status-pending { background-color: #dbeafe; color: #1e40af; }
        .status-completed { background-color: #dcfce7; color: #166534; }
        .status-cancelled { background-color: #f3f4f6; color: #374151; }
        .status-paid { background-color: #dcfce7; color: #166534; }
        .status-unpaid { background-color: #fee2e2; color: #991b1b; }
        .status-partial { background-color: #fef3c7; color: #92400e; }
        @yield('styles')
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    @yield('body')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
        }
    </script>
    @stack('scripts')
</body>
</html>
