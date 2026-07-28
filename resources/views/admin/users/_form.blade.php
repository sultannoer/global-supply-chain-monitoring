<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nama lengkap</label><input name="name" value="{{ old('name', $user->name ?? '') }}" class="form-control" required maxlength="255"></div>
    <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" value="{{ old('email', $user->email ?? '') }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Kata sandi {{ isset($user) ? '(kosongkan jika tetap)' : '' }}</label><input name="password" type="password" class="form-control" {{ isset($user) ? '' : 'required' }} minlength="8"></div>
    <div class="col-md-6"><label class="form-label">Konfirmasi kata sandi</label><input name="password_confirmation" type="password" class="form-control" {{ isset($user) ? '' : 'required' }} minlength="8"></div>
    <div class="col-md-6"><label class="form-label">Role</label><select name="role" class="form-select" required><option value="user" @selected(old('role', $user->role ?? 'user') === 'user')>User</option><option value="admin" @selected(old('role', $user->role ?? '') === 'admin')>Administrator</option></select></div>
</div>
<div class="d-flex gap-2 mt-4"><button class="btn btn-info fw-semibold"><i class="bi bi-save me-1"></i>Simpan</button><a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a></div>
