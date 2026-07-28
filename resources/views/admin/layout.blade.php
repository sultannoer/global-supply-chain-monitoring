<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') · GeoPort Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --admin-bg:#060b15; --admin-panel:#0d1829; --admin-line:rgba(71,120,162,.42); --admin-cyan:#22d3ee; --admin-muted:#9aacbf; }
        body { min-height:100vh; background:radial-gradient(circle at 75% -12%,rgba(12,78,125,.22),transparent 35rem),linear-gradient(135deg,#060b15,#081120 55%,#06111d); color:#e5e7eb; }
        body::before { content:''; position:fixed; inset:0; pointer-events:none; opacity:.22; background-image:linear-gradient(rgba(56,189,248,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(56,189,248,.04) 1px,transparent 1px); background-size:44px 44px; mask-image:linear-gradient(to bottom,black,transparent 82%); z-index:-1; }
        .admin-shell { min-height:100vh; display:grid; grid-template-columns:250px minmax(0,1fr); }
        .admin-sidebar { background:linear-gradient(180deg,rgba(6,14,29,.99),rgba(7,18,34,.98)); border-right:1px solid rgba(34,211,238,.24); padding:1.25rem 1rem; position:sticky; top:0; height:100vh; box-shadow:12px 0 28px rgba(0,0,0,.16); }
        .admin-brand { color:#fff; text-decoration:none; font-size:1.15rem; font-weight:800; display:flex; align-items:center; gap:.65rem; }
        .admin-brand i { color:var(--admin-cyan); }
        .admin-nav .nav-link { color:var(--admin-muted); border:1px solid transparent; border-radius:.6rem; padding:.7rem .8rem; display:flex; align-items:center; gap:.7rem; transition:all .18s ease; }
        .admin-nav .nav-link:hover,.admin-nav .nav-link.active { color:#fff; background:rgba(34,211,238,.12); border-color:rgba(34,211,238,.2); transform:translateX(3px); }
        .admin-nav .nav-link.active i { color:var(--admin-cyan); }
        .admin-main { min-width:0; }
        .admin-topbar { min-height:68px; border-bottom:1px solid rgba(34,211,238,.18); background:linear-gradient(90deg,rgba(8,20,37,.94),rgba(13,35,55,.8)); padding:.8rem 1.5rem; display:flex; align-items:center; justify-content:space-between; }
        .admin-content { padding:1.5rem; }
        .admin-card { background:linear-gradient(145deg,rgba(15,29,49,.98),rgba(7,16,29,.98)); border:1px solid var(--admin-line); border-radius:.8rem; box-shadow:0 12px 28px rgba(0,0,0,.16); transition:transform .18s ease,border-color .18s ease,box-shadow .18s ease; }
        .admin-card:hover { transform:translateY(-2px); border-color:rgba(34,211,238,.54); box-shadow:0 18px 34px rgba(0,0,0,.26),0 0 0 1px rgba(34,211,238,.07); }
        .admin-card-linkable { cursor:pointer; }
        .admin-card-linkable:focus-visible { outline:2px solid var(--admin-cyan); outline-offset:3px; }
        .admin-table { --bs-table-bg:transparent; --bs-table-color:#e5e7eb; --bs-table-border-color:var(--admin-line); margin-bottom:0; }
        .admin-table thead th { color:#94a3b8; font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; }
        .form-control,.form-select { background:#06101e; border-color:rgba(91,137,175,.48); color:#f8fafc; }
        .form-control:focus,.form-select:focus { background:#06101e; color:#fff; border-color:#22d3ee; box-shadow:0 0 0 .2rem rgba(34,211,238,.12); }
        .form-control::placeholder { color:#64748b; }
        .form-label { color:#dbeafe; font-weight:600; }
        .btn { font-weight:600; transition:transform .18s ease,box-shadow .18s ease; }
        .btn:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 7px 16px rgba(0,0,0,.22); }
        .table-hover tbody tr { transition:background-color .18s ease; }
        .table-hover tbody tr:hover { background:rgba(34,211,238,.055)!important; }
        .text-secondary { color:var(--admin-muted)!important; }
        .admin-content .alert { border:1px solid rgba(255,255,255,.12)!important; box-shadow:inset 3px 0 0 currentColor,0 10px 22px rgba(0,0,0,.1); }
        ::-webkit-scrollbar { width:10px; height:10px; } ::-webkit-scrollbar-thumb { background:#334155; border-radius:10px; } ::-webkit-scrollbar-track { background:#0b1220; }
        .pagination { --bs-pagination-bg:#111827; --bs-pagination-border-color:#334155; --bs-pagination-color:#cbd5e1; --bs-pagination-hover-bg:#1e293b; --bs-pagination-hover-color:#fff; --bs-pagination-active-bg:#0891b2; --bs-pagination-active-border-color:#0891b2; --bs-pagination-disabled-bg:#0f172a; --bs-pagination-disabled-border-color:#334155; }
        .admin-mobile-drawer { width:min(88vw,365px)!important; background:linear-gradient(155deg,#0d1d33,#050c17 74%)!important; color:#eaf2ff; border-color:rgba(34,211,238,.48)!important; }
        .admin-mobile-drawer .offcanvas-header { background:linear-gradient(90deg,rgba(13,47,73,.6),rgba(7,17,29,.7)); }
        .admin-mobile-drawer .admin-sidebar { display:flex!important; position:static!important; height:auto!important; min-height:100%!important; padding:0!important; background:transparent!important; border:0!important; box-shadow:none!important; }
        @media (max-width: 900px) { .admin-shell { grid-template-columns:1fr; } .admin-shell > .admin-sidebar { display:none!important; } .admin-content { padding:1rem; } .admin-topbar { padding:.75rem 1rem; } }
    </style>
    @stack('styles')
</head>
<body>
<div class="admin-shell">
    <aside id="adminDesktopSidebar" class="admin-sidebar d-flex flex-column">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand"><i class="bi bi-anchor-fill"></i><span>GeoPort Analytics<small class="d-block text-info text-uppercase" style="font-size:.55rem;letter-spacing:.12em;">Admin Console</small></span></a>
        <div class="small text-uppercase text-secondary mt-4 mb-2 px-2">Management</div>
        <nav class="nav flex-column admin-nav gap-1">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-1x2-fill"></i>Ringkasan</a>
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people-fill"></i>Manajemen User</a>
            <a class="nav-link {{ request()->routeIs('admin.ports.*') ? 'active' : '' }}" href="{{ route('admin.ports.index') }}"><i class="bi bi-anchor"></i>Dataset Pelabuhan</a>
            <a class="nav-link {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}" href="{{ route('admin.articles.index') }}"><i class="bi bi-file-earmark-text-fill"></i>Artikel Analisis</a>
            <a class="nav-link {{ request()->routeIs('admin.watchlists.*') ? 'active' : '' }}" href="{{ route('admin.watchlists.index') }}"><i class="bi bi-star-fill"></i>Monitoring Favorit</a>
        </nav>
        <div class="mt-auto border-top border-secondary border-opacity-25 pt-3">
            <a class="nav-link text-info px-2" href="{{ route('ports.index') }}"><i class="bi bi-radar me-2"></i>Kembali ke Radar</a>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-2"><button class="btn btn-sm btn-outline-info d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileNavigation" aria-controls="adminMobileNavigation" aria-label="Buka menu admin"><i class="bi bi-list fs-5"></i></button><div><div class="fw-semibold">@yield('page-heading', 'Admin Dashboard')</div><small class="text-secondary">Kontrol data dan akses sistem</small></div></div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block"><small class="d-block text-secondary text-uppercase" style="font-size:.6rem;letter-spacing:.08em;">Akun aktif</small><div class="small fw-semibold text-white">{{ auth()->user()->name }}</div><span class="badge bg-warning text-dark text-uppercase" style="font-size:.58rem;">Admin</span></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Keluar</button></form>
            </div>
        </header>
        <main class="admin-content">
            @if(session('success'))<div class="alert alert-success border-0"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger border-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger"><div class="fw-semibold mb-1">Periksa data berikut:</div><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>
</div>
<div class="offcanvas offcanvas-start admin-mobile-drawer d-md-none" tabindex="-1" id="adminMobileNavigation" aria-labelledby="adminMobileNavigationLabel">
    <div class="offcanvas-header border-bottom border-info border-opacity-25"><div><div class="small text-info text-uppercase fw-bold" style="letter-spacing:.1em;">Admin command</div><h5 class="offcanvas-title fw-bold mb-0" id="adminMobileNavigationLabel">Menu Admin</h5></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button></div>
    <div id="adminMobileNavigationContent" class="offcanvas-body"></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const source = document.getElementById('adminDesktopSidebar');
        const target = document.getElementById('adminMobileNavigationContent');
        if (!source || !target) return;
        const copy = source.cloneNode(true);
        copy.removeAttribute('id');
        target.replaceChildren(copy);
    })();
    document.querySelectorAll('form[method="POST"]:not([data-no-submit-feedback])').forEach(form => form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        if (!button || button.disabled) return;
        button.disabled = true; button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
    }));
    document.querySelectorAll('.admin-card').forEach(card => {
        const links = [...card.querySelectorAll('a[href]')].filter(link => !link.closest('form'));
        if (links.length !== 1 || card.querySelector('button, input, select, textarea, form')) return;
        const open = () => links[0].click();
        card.classList.add('admin-card-linkable'); card.tabIndex = 0; card.setAttribute('role', 'link');
        card.addEventListener('click', event => { if (!event.target.closest('a')) open(); });
        card.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); open(); } });
    });
</script>
@stack('scripts')
</body>
</html>
