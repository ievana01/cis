@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('supplier.store') }}">
        @csrf
        <h4 class="font-weight-bold">Tambah Pemasok</h4>
        <div class="form-group">
            <label for="name">Nama Pemasok</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama pemasok">
        </div>
        <div class="form-group">
            <label for="phone_number">Nomor Telepon</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" aria-describedby="phone_number"
                placeholder="Masukkan nomor telepon">
        </div>
        <div class="form-group">
            <label for="address">Alamat</label>
            <input type="text" class="form-control" id="address" name="address" aria-describedby="address"
                placeholder="Masukkan alamat pemasok">
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{route('supplier.index')}}" class="btn btn-danger">Batal</a>
    </form>
@endsection
