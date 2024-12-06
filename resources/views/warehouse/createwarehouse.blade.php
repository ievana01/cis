@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('warehouse.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">Nama Gudang</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama gudang">
        </div>
        <div class="form-group">
            <label for="address">Alamat</label>
            <input type="text" class="form-control" id="address" name="address" aria-describedby="address"
                placeholder="Masukkan alamat">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection
