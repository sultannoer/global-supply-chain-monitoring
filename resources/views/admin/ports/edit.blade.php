@extends('admin.layout')
@section('title', 'Edit Pelabuhan')
@section('page-heading', 'Edit Pelabuhan')
@section('content')
<div class="mb-4"><h1 class="h3 fw-bold mb-1">Edit {{ $port->name }}</h1><p class="text-secondary mb-0">Perbarui koordinat dan data operasional pelabuhan.</p></div>
<form method="POST" action="{{ route('admin.ports.update', $port) }}" class="admin-card p-4">@csrf @method('PUT') @include('admin.ports._form')</form>
@endsection
