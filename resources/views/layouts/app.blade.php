<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚓ Global Port Intelligence Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @stack('styles')
    <style>
        :root { --gp-bg:#060b15; --gp-panel:#0d1829; --gp-panel-soft:#101f35; --gp-line:rgba(71,120,162,.42); --gp-text:#eaf2ff; --gp-muted:#9aacbf; --gp-cyan:#22d3ee; --gp-blue:#1677ff; --gp-green:#16c784; --gp-gold:#f7b500; }
        body { min-height:100vh; background:radial-gradient(circle at 78% -10%,rgba(12,78,125,.22),transparent 34rem),linear-gradient(135deg,#060b15 0%,#081120 54%,#06111d 100%); color:var(--gp-text); }
        body::before { content:''; position:fixed; inset:0; pointer-events:none; opacity:.24; background-image:linear-gradient(rgba(56,189,248,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(56,189,248,.04) 1px,transparent 1px); background-size:44px 44px; mask-image:linear-gradient(to bottom,black,transparent 78%); z-index:-1; }
        .navbar { background:linear-gradient(100deg,rgba(8,18,34,.98),rgba(13,33,54,.97)) !important; border-bottom:1px solid rgba(34,211,238,.26); box-shadow:0 9px 26px rgba(0,0,0,.2); }
        .navbar-brand { letter-spacing:.02em; text-shadow:0 0 18px rgba(34,211,238,.22); }
        a, .btn, .nav-link, .card { transition:color .18s ease, background-color .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease; }
        .btn { font-weight:600; border-radius:.5rem; }
        .btn:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 8px 18px rgba(2,6,23,.2); }
        .btn:focus-visible, a:focus-visible, .form-control:focus, .form-select:focus { outline:0; box-shadow:0 0 0 .2rem rgba(34,211,238,.2) !important; }
        .form-control, .form-select { background-color:rgba(4,10,19,.88); border-color:rgba(91,137,175,.48); color:var(--gp-text); }
        .form-control:focus, .form-select:focus { background-color:#06101e; color:#fff; border-color:var(--gp-cyan); }
        .form-select option { background:#0a1525; color:#eef7ff; }
        .form-control::placeholder { color:#7f8da3; opacity:1; }
        .form-text, .text-muted, .text-secondary { color:var(--gp-muted) !important; }
        .card, .modal-content { background:linear-gradient(145deg,rgba(15,29,49,.97),rgba(7,16,29,.98)) !important; border-color:var(--gp-line) !important; box-shadow:0 12px 30px rgba(0,0,0,.16); }
        .card-header, .card-footer { background:linear-gradient(90deg,rgba(16,39,64,.78),rgba(9,21,36,.38)) !important; border-color:var(--gp-line) !important; }
        .table { --bs-table-bg:transparent; --bs-table-color:var(--gp-text); --bs-table-border-color:var(--gp-line); }
        .table-dark { --bs-table-bg:transparent; --bs-table-striped-bg:rgba(31,58,88,.24); --bs-table-hover-bg:rgba(34,211,238,.075); --bs-table-color:var(--gp-text); --bs-table-border-color:var(--gp-line); }
        .table > :not(caption) > * > * { border-color:var(--gp-line); }
        .table-hover > tbody > tr { transition:background-color .18s ease; }
        .table-hover > tbody > tr:hover { background-color:rgba(34,211,238,.06) !important; }
        main .card { border-color:var(--gp-line); transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease,background .2s ease; }
        main .card:hover { border-color:rgba(34,211,238,.56) !important; transform:translateY(-2px); box-shadow:0 18px 32px rgba(0,0,0,.25),0 0 0 1px rgba(34,211,238,.08); }
        main .card-linkable { cursor:pointer; }
        main .card-linkable:focus-visible { outline:2px solid var(--gp-cyan); outline-offset:3px; }
        main .table tbody tr { transition:background-color .18s ease,transform .18s ease; }
        main .table tbody tr:hover { transform:scale(1.002); }
        .page-reveal { animation:gp-reveal .35s ease both; }
        .back-dashboard { display:inline-flex; align-items:center; gap:.45rem; padding:.55rem .9rem; border:1px solid rgba(56,189,248,.45); border-radius:.5rem; color:#7dd3fc !important; background:rgba(14,116,144,.14); font-weight:600; text-decoration:none; transition:.2s ease; }
        .back-dashboard:hover { color:#fff !important; background:rgba(14,116,144,.35); border-color:#38bdf8; transform:translateX(-2px); }
        .global-mobile-drawer { width:min(88vw,365px)!important; color:var(--gp-text); background:linear-gradient(155deg,#0d1d33,#050c17 74%)!important; border-color:rgba(34,211,238,.48)!important; box-shadow:0 0 42px rgba(0,0,0,.52),0 0 25px rgba(34,211,238,.12); }
        .global-mobile-drawer .offcanvas-header { background:linear-gradient(90deg,rgba(13,47,73,.6),rgba(7,17,29,.7)); border-color:var(--gp-line)!important; }
        .global-mobile-drawer .nav-link { color:#aabbd1; border:1px solid transparent; border-radius:.55rem; padding:.72rem .8rem; display:flex; gap:.7rem; align-items:center; }
        .global-mobile-drawer .nav-link:hover,.global-mobile-drawer .nav-link:focus { color:#fff; background:rgba(34,211,238,.1); border-color:rgba(34,211,238,.28); }
        @media (max-width:991.98px) { .navbar .container { flex-wrap:nowrap; gap:.45rem; padding-inline:.7rem; } .navbar-brand { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:clamp(.92rem,3.9vw,1.1rem); } .navbar .ms-auto { flex-shrink:0; gap:.35rem!important; } .navbar .btn { padding:.38rem .5rem; } }
        @media (max-width:575.98px) { .navbar .nav-quick-label { display:none; } .navbar .btn { min-width:2.3rem; } }
        main h1, main h2, main h3 { text-shadow:0 0 22px rgba(34,211,238,.1); }
        main .text-white-50 { color:var(--gp-muted) !important; }
        main .input-group-text { background:rgba(10,29,48,.92); color:var(--gp-cyan); border-color:rgba(91,137,175,.48); }
        main .list-group-item { background:linear-gradient(90deg,rgba(13,27,46,.94),rgba(7,17,29,.94)) !important; color:var(--gp-text) !important; border-color:var(--gp-line) !important; }
        main .list-group-item:hover { background:linear-gradient(90deg,rgba(17,48,73,.98),rgba(10,28,45,.98)) !important; }
        main .alert { border-width:1px; box-shadow:inset 3px 0 0 currentColor,0 10px 22px rgba(0,0,0,.1); }
        main .badge { box-shadow:inset 0 1px 0 rgba(255,255,255,.12); letter-spacing:.015em; }
        main .btn-primary, main .btn-info { border-color:rgba(77,215,255,.72); background:linear-gradient(135deg,#087cc6,#13bddd); color:#fff; }
        main .btn-primary:hover, main .btn-info:hover { background:linear-gradient(135deg,#1195e9,#25d5e9); color:#fff; }
        main .btn-outline-info { border-color:rgba(34,211,238,.6); color:#72e6f7; }
        main .btn-outline-info:hover { background:rgba(34,211,238,.16); color:#fff; }
        main .table-responsive { border-radius:.45rem; }
        ::-webkit-scrollbar { width:10px; height:10px; } ::-webkit-scrollbar-thumb { background:linear-gradient(#1f5b83,#174261); border-radius:10px; border:2px solid #07101d; } ::-webkit-scrollbar-track { background:#07101d; }
        @keyframes gp-reveal { from { opacity:0; transform:translateY(7px); } to { opacity:1; transform:none; } }
        @media (prefers-reduced-motion:reduce) { *,*::before,*::after { animation-duration:.01ms!important; animation-iteration-count:1!important; transition-duration:.01ms!important; } }
    </style>
</head>
<body class="bg-dark">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('ports.index') }}">⚓ GeoPort Analytics</a>
            <div class="d-flex gap-2 ms-auto">
                <button class="btn btn-sm btn-outline-info d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#globalMobileNavigation" aria-controls="globalMobileNavigation" aria-label="Buka menu navigasi">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <a class="btn btn-sm btn-outline-warning" href="{{ auth()->user()?->isAdmin() ? route('admin.watchlists.index') : route('watchlists.index') }}">
                    <i class="bi bi-star-fill me-1"></i><span class="nav-quick-label">{{ auth()->user()?->isAdmin() ? 'Favorit User' : 'Favorit' }}</span>
                </a>
                <a class="btn btn-sm btn-outline-info" href="{{ auth()->user()?->isAdmin() ? route('admin.dashboard') : route('login') }}"><i class="bi bi-shield-lock-fill me-1"></i><span class="nav-quick-label">{{ auth()->user()?->isAdmin() ? 'Admin' : 'Login Admin' }}</span></a>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-start global-mobile-drawer d-lg-none" tabindex="-1" id="globalMobileNavigation" aria-labelledby="globalMobileNavigationLabel">
        <div class="offcanvas-header border-bottom">
            <div>
                <div class="small text-info text-uppercase fw-bold" style="letter-spacing:.1em;"><i class="bi bi-broadcast-pin me-1"></i>GeoPort command</div>
                <h5 class="offcanvas-title fw-bold mb-0" id="globalMobileNavigationLabel">Navigasi Operasi</h5>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body p-3">
            <nav class="nav flex-column gap-1">
                <a class="nav-link" href="{{ route('ports.index') }}"><i class="bi bi-grid-1x2-fill text-info"></i>Live Dashboard</a>
                <a class="nav-link" href="{{ route('risk-scores.index') }}"><i class="bi bi-shield-exclamation text-warning"></i>Risk Score Engine</a>
                <a class="nav-link" href="{{ route('country-comparison.index') }}"><i class="bi bi-bar-chart text-info"></i>Country Comparison</a>
                <a class="nav-link" href="{{ route('news-sentiment.index') }}"><i class="bi bi-newspaper text-info"></i>News Sentiment</a>
                <a class="nav-link" href="{{ route('trends.index') }}"><i class="bi bi-graph-up-arrow text-success"></i>Historical Trends</a>
                <a class="nav-link" href="{{ route('cargo.create') }}"><i class="bi bi-box-seam text-warning"></i>Input Cargo</a>
                <a class="nav-link" href="{{ route('cargo.history') }}"><i class="bi bi-clock-history text-danger"></i>Log Riwayat</a>
                <a class="nav-link" href="{{ auth()->user()?->isAdmin() ? route('admin.watchlists.index') : route('watchlists.index') }}"><i class="bi bi-star-fill text-warning"></i>Monitoring Favorit</a>
                @if(auth()->user()?->isAdmin())
                    <a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-shield-lock-fill text-info"></i>Admin Console</a>
                @endif
                @auth
                    <div class="border-top border-secondary border-opacity-25 mt-2 pt-2"><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="nav-link w-100 text-start text-danger"><i class="bi bi-box-arrow-right"></i>Keluar dari akun</button></form></div>
                @endauth
            </nav>
        </div>
    </div>

    <main class="page-reveal">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        document.querySelectorAll('form[method="POST"]:not([data-no-submit-feedback])').forEach(form => form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (!button || button.disabled) return;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
        }));
        document.querySelectorAll('main .card').forEach(card => {
            const links = [...card.querySelectorAll('a[href]')].filter(link => !link.closest('form'));
            const controls = card.querySelectorAll('button, input, select, textarea, form');
            if (links.length !== 1 || controls.length) return;
            const link = links[0];
            card.classList.add('card-linkable'); card.tabIndex = 0; card.setAttribute('role', 'link');
            const open = () => link.click();
            card.addEventListener('click', event => { if (!event.target.closest('a')) open(); });
            card.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); open(); } });
        });
    </script>
    @stack('scripts')
</body>
</html>
