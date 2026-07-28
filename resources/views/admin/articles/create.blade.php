@extends('admin.layout')
@section('title', 'Tulis Artikel')
@section('page-heading', 'Tulis Artikel')
@section('content')
<div class="mb-4"><h1 class="h3 fw-bold mb-1">Artikel Analisis Baru</h1><p class="text-secondary mb-0">Catat hasil analisis manual untuk kebutuhan intelligence dan keputusan bisnis.</p></div>
<form method="POST" action="{{ route('admin.articles.store') }}" class="admin-card p-4">@csrf @include('admin.articles._form')</form>
@endsection
