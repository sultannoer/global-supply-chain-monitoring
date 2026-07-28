<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User · GeoPort Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background:#070b13; } .auth-card { background:linear-gradient(145deg,#1a2434,#101722)!important; border-color:rgba(125,211,252,.22)!important; box-shadow:0 22px 60px rgba(0,0,0,.38)!important; }
        .auth-card .form-control { min-height:46px; } .auth-card .form-control:focus { border-color:#22d3ee; box-shadow:0 0 0 .2rem rgba(34,211,238,.16); }
        .auth-card .btn { transition:transform .18s ease,box-shadow .18s ease; } .auth-card .btn:hover { transform:translateY(-2px); box-shadow:0 10px 22px rgba(34,211,238,.2); }
    </style>
</head>
<body class="bg-black text-white">
<main class="min-vh-100 d-flex align-items-center justify-content-center p-3" style="background:radial-gradient(circle at 80% 10%,rgba(8,145,178,.25),transparent 35%),#070b13;">
    <div class="card auth-card bg-dark text-white border-secondary border-opacity-25 shadow-lg rounded-4" style="width:min(440px,100%);">
        <div class="card-body p-4 p-md-5">
            <div class="text-center my-3"><div class="display-5 text-info"><i class="bi bi-person-plus-fill"></i></div><h1 class="h3 fw-bold mt-2">Daftar Akun User</h1><p class="text-white-50 mb-0">Buat akun untuk memantau data GeoPort.</p></div>
            @if($errors->any())<div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('register.store') }}" class="d-grid gap-3 mt-4">
                @csrf
                <div><label for="name" class="form-label">Nama</label><input id="name" name="name" type="text" value="{{ old('name') }}" class="form-control bg-black text-white border-secondary" required autofocus autocomplete="name"></div>
                <div><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control bg-black text-white border-secondary" required autocomplete="email"></div>
                <div><label for="password" class="form-label">Kata sandi</label><div class="input-group"><input id="password" name="password" type="password" class="form-control bg-black text-white border-secondary" required minlength="8" autocomplete="new-password"><button class="btn btn-outline-secondary password-toggle" type="button" data-target="password"><i class="bi bi-eye"></i></button></div><small class="text-white-50">Minimal 8 karakter.</small></div>
                <div><label for="password_confirmation" class="form-label">Konfirmasi kata sandi</label><div class="input-group"><input id="password_confirmation" name="password_confirmation" type="password" class="form-control bg-black text-white border-secondary" required minlength="8" autocomplete="new-password"><button class="btn btn-outline-secondary password-toggle" type="button" data-target="password_confirmation"><i class="bi bi-eye"></i></button></div></div>
                <button class="btn btn-info fw-semibold py-2"><i class="bi bi-person-check-fill me-1"></i>Buat Akun User</button>
            </form>
            <div class="text-center text-white-50 small mt-4">Sudah punya akun? <a href="{{ route('login') }}" class="text-info text-decoration-none fw-semibold">Kembali ke login</a></div>
        </div>
    </div>
</main>
<script>document.querySelectorAll('.password-toggle').forEach(button=>button.addEventListener('click',()=>{const input=document.getElementById(button.dataset.target);const reveal=input.type==='password';input.type=reveal?'text':'password';button.innerHTML='<i class="bi bi-eye'+(reveal?'-slash':'')+'"></i>';}));</script>
</body>
</html>
