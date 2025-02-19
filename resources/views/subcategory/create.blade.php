@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('subCategory.store') }}">
        @csrf
        <div class="form-group">
            <label for="category_id">ID Kategori</label>
            <input type="text" readonly class="form-control" name="category_id" value="{{ $category->id_category }}">
        </div>
        <div class="form-group">
            <label for="name">Nama Kategori</label>
            <input type="text" readonly class="form-control" name="name" value="{{ $category->name }}">
        </div>
        <div class="form-group">
            <label for="code_sub_category">Kode Sub Kategori</label>
            <input type="text" class="form-control" name="code_sub_category" id="code_sub_category" placeholder="Masukkan kode sub kategori">
        </div>
        <div class="form-group">
            <label for="name">Nama Sub Kategori</label>
            <input type="text" class="form-control" name="name" id="name" placeholder="Masukkan nama sub kategori">
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{route('category.index')}}" class="btn btn-danger">Batal</a>
    </form>
@endsection
