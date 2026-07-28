@extends('admin.layout')
@section('title', 'Edit User')
@section('page-heading', 'Edit User')
@section('content')
<div class="mb-4"><h1 class="h3 fw-bold mb-1">Edit {{ $user->name }}</h1><p class="text-secondary mb-0">Perbarui profil, kata sandi, atau role user.</p></div>
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-card p-4">@csrf @method('PUT') @include('admin.users._form')</form>
@endsection
