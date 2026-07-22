<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚓ Global Port Intelligence Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @stack('styles')
    <style>
        .back-dashboard { display:inline-flex; align-items:center; gap:.45rem; padding:.55rem .9rem; border:1px solid rgba(56,189,248,.45); border-radius:.5rem; color:#7dd3fc !important; background:rgba(14,116,144,.14); font-weight:600; text-decoration:none; transition:.2s ease; }
        .back-dashboard:hover { color:#fff !important; background:rgba(14,116,144,.35); border-color:#38bdf8; transform:translateX(-2px); }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('ports.index') }}">⚓ GeoPort Analytics</a>
            <a class="btn btn-sm btn-outline-warning ms-auto" href="{{ route('watchlists.index') }}"><i class="bi bi-star-fill me-1"></i>Favorit</a>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
