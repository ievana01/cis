@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('subCategory.update', $subCategory->id_sub_category) }}">
        @csrf
        @method('PUT')
        <h4 class="font-weight-bold">Edit Sub Kategori</h4>
        <div class="form group">
            <label for="id_sub_category">ID Sub Kategori</label>
            <input type="text" class="form-control" name="id_sub_category" id="id_sub_category"
                value="{{ $subCategory->id_sub_category }}" disabled>
        </div>
        <div class="form-group">
            <label for="code_sub_category">Kode Sub Kategori</label>
            <input type="text" class="form-control" id="code_sub_category" name="code_sub_category"
                aria-describedby="code_sub_category" placeholder="Masukkan kode sub kategori"
                value="{{ $subCategory->code_sub_category }}">
        </div>
        <div class="form-group">
            <label for="name">Nama Kategori</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama kategori" value="{{ $subCategory->name }}">
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('category.index') }}" class="btn btn-danger">Batal</a>
    </form>
@endsection
