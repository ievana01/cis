@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('category.store') }}">
        @csrf
        <h4 class="font-weight-bold">Kategori Baru</h4>
        <div class="form-group">
            <label for="name">Nama Kategori</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama kategori">
        </div>
        <div class="form-group">
            <label for="code_category">Kode Kategori</label>
            <input type="text" class="form-control" id="code_category" name="code_category"
                placeholder="Masukkan kode kategori">
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('category.index') }}" type="button" class="btn btn-danger">Batal</a>
    </form>
@endsection
