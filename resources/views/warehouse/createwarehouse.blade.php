@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('warehouse.store') }}">
        @csrf
        <h4 class="font-weight-bold">Gudang Baru</h4>
        <div class="form-group">
            <label for="name">Nama Gudang</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama gudang">
        </div>
        <div class="form-group">
            <label for="address">Alamat</label>
            <input type="text" class="form-control" id="address" name="address" aria-describedby="address"
                placeholder="Masukkan alamat gudang">
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('warehouse.index') }}" type="button" class="btn btn-danger">Batal</a>
    </form>
@endsection
