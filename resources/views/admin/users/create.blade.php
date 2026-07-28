@extends('admin.layout')
@section('title', 'Tambah User')
@section('page-heading', 'Tambah User')
@section('content')
<div class="mb-4"><h1 class="h3 fw-bold mb-1">Tambah User</h1><p class="text-secondary mb-0">Buat akun baru dan tentukan hak aksesnya.</p></div>
<form method="POST" action="{{ route('admin.users.store') }}" class="admin-card p-4">@csrf @include('admin.users._form')</form>
@endsection
