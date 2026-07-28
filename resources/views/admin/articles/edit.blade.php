@extends('admin.layout')
@section('title', 'Edit Artikel')
@section('page-heading', 'Edit Artikel')
@section('content')
<div class="mb-4"><h1 class="h3 fw-bold mb-1">Edit Artikel Analisis</h1><p class="text-secondary mb-0">Perbarui isi dan status publikasi artikel.</p></div>
<form method="POST" action="{{ route('admin.articles.update', $article) }}" class="admin-card p-4">@csrf @method('PUT') @include('admin.articles._form')</form>
@endsection
