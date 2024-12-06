@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('category.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">Nama Kategori</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama kategori">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection
