@extends('admin.layout')
@section('title', 'Manajemen User')
@section('page-heading', 'Manajemen User')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 fw-bold mb-1">User & Hak Akses</h1><p class="text-secondary mb-0">Kelola akun administrator dan pengguna sistem.</p></div><a href="{{ route('admin.users.create') }}" class="btn btn-info fw-semibold"><i class="bi bi-person-plus-fill me-1"></i>Tambah User</a></div>
<div class="admin-card overflow-hidden">
    <div class="p-3 border-bottom border-secondary border-opacity-25"><form method="GET" class="d-flex gap-2"><input name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama atau email..."><button class="btn btn-outline-info"><i class="bi bi-search"></i></button>@if($search)<a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Reset</a>@endif</form></div>
    <div class="table-responsive"><table class="table admin-table align-middle"><thead><tr><th>User</th><th>Role</th><th>Dibuat</th><th class="text-end">Aksi</th></tr></thead><tbody>
        @forelse($users as $user)<tr><td><div class="fw-semibold">{{ $user->name }}</div><small class="text-secondary">{{ $user->email }}</small></td><td><span class="badge text-bg-{{ $user->role === 'admin' ? 'info' : 'secondary' }}">{{ strtoupper($user->role) }}</span></td><td>{{ $user->created_at?->format('d M Y') }}</td><td class="text-end"><a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil"></i></a><form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Hapus user ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" @disabled(auth()->id() === $user->id)><i class="bi bi-trash"></i></button></form></td></tr>
        @empty<tr><td colspan="4" class="text-center text-secondary py-5">User tidak ditemukan.</td></tr>@endforelse
    </tbody></table></div>
    @if($users->hasPages())<div class="p-3 border-top border-secondary border-opacity-25">{{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}</div>@endif
</div>
@endsection
