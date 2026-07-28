<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · GeoPort Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background:#070b13; } .auth-card { background:linear-gradient(145deg,#1a2434,#101722)!important; border-color:rgba(125,211,252,.22)!important; box-shadow:0 22px 60px rgba(0,0,0,.38)!important; }
        .auth-card .form-control { min-height:46px; } .auth-card .form-control:focus { border-color:#22d3ee; box-shadow:0 0 0 .2rem rgba(34,211,238,.16); }
        .auth-card .btn { transition:transform .18s ease,box-shadow .18s ease; } .auth-card .btn:hover { transform:translateY(-2px); box-shadow:0 10px 22px rgba(34,211,238,.2); }
        .auth-card .btn-check:checked + .btn { background:#22c7e6; color:#07111b; border-color:#67e8f9; box-shadow:0 0 0 .15rem rgba(34,211,238,.18); }
    </style>
</head>
<body class="bg-black text-white">
<main class="min-vh-100 d-flex align-items-center justify-content-center p-3" style="background:radial-gradient(circle at 20% 10%,rgba(8,145,178,.25),transparent 35%),#070b13;">
    <div class="card auth-card bg-dark text-white border-secondary border-opacity-25 shadow-lg rounded-4" style="width:min(440px,100%);">
        <div class="card-body p-4 p-md-5">
            <div class="text-center my-4"><div class="display-5 text-info"><i class="bi bi-globe2"></i></div><h1 class="h3 fw-bold mt-2">GeoPort Analytics</h1><p class="text-white-50 mb-0">Pilih jenis akun untuk melanjutkan.</p></div>
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('login.attempt') }}" class="d-grid gap-3">
                @csrf
                <div><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control bg-black text-white border-secondary" required autofocus autocomplete="email"></div>
                <div><label for="password" class="form-label">Kata sandi</label><div class="input-group"><input id="password" name="password" type="password" class="form-control bg-black text-white border-secondary" required autocomplete="current-password"><button class="btn btn-outline-secondary password-toggle" type="button" data-target="password" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button></div></div>
                <div><label class="form-label mb-2">Masuk sebagai</label><div class="row g-2">
                    <div class="col-6"><input class="btn-check" type="radio" name="role" id="roleAdmin" value="admin" @checked(old('role', 'admin') === 'admin')><label class="btn btn-outline-info w-100 text-start p-3" for="roleAdmin"><i class="bi bi-shield-lock-fill d-block fs-4 mb-1"></i><span class="fw-semibold d-block">Admin</span><small class="text-white-50">Kelola sistem</small></label></div>
                    <div class="col-6"><input class="btn-check" type="radio" name="role" id="roleUser" value="user" @checked(old('role') === 'user')><label class="btn btn-outline-light w-100 text-start p-3" for="roleUser"><i class="bi bi-person-fill d-block fs-4 mb-1"></i><span class="fw-semibold d-block">User</span><small class="text-white-50">Pantau data</small></label></div>
                </div></div>
                <label class="form-check"><input class="form-check-input" type="checkbox" name="remember" value="1"><span class="form-check-label text-white-50">Ingat sesi login</span></label>
                <button class="btn btn-info fw-semibold py-2"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</button>
            </form>
            <div class="text-center text-white-50 small mt-4">Belum punya akun User? <a href="{{ route('register') }}" class="text-info text-decoration-none fw-semibold">Daftar sekarang</a></div>
        </div>
    </div>
</main>
<script>document.querySelectorAll('.password-toggle').forEach(button=>button.addEventListener('click',()=>{const input=document.getElementById(button.dataset.target);const reveal=input.type==='password';input.type=reveal?'text':'password';button.innerHTML='<i class="bi bi-eye'+(reveal?'-slash':'')+'"></i>';button.setAttribute('aria-label',reveal?'Sembunyikan kata sandi':'Tampilkan kata sandi');}));</script>
</body>
</html>
