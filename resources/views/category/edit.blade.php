@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('category.update', $category->id_category) }}">
        @csrf
        @method('PUT')
        <h4 class="font-weight-bold">Edit Kategori</h4>
        <div class="form group">
            <label for="id_category">ID Kategori</label>
            <input type="text" class="form-control" name="id_category" id="id_category" value="{{ $category->id_category }}"
                disabled>
        </div>
        <div class="form-group">
            <label for="name">Nama Kategori</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama kategori" value="{{ $category->name }}">
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
@endsection
