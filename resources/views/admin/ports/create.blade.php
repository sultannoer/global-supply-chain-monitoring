@extends('admin.layout')
@section('title', 'Tambah Pelabuhan')
@section('page-heading', 'Tambah Pelabuhan')
@section('content')
<div class="mb-4"><h1 class="h3 fw-bold mb-1">Tambah Dataset Pelabuhan</h1><p class="text-secondary mb-0">Daftarkan satu titik pelabuhan baru ke radar global.</p></div>
<form method="POST" action="{{ route('admin.ports.store') }}" class="admin-card p-4">@csrf @include('admin.ports._form')</form>
@endsection
