@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('supplier.update', $supplier->id_supplier) }}">
        @csrf
        @method('PUT')
        <h4 class="font-weight-bold">Edit Pemasok</h4>
        <div class="form-group">
            <label for="id_supplier">ID Pemasok</label>
            <input class="form-control" type="text" name="id_supplier" id="id_supplier" value="{{ $supplier->id_supplier }}"
                disabled>
        </div>
        <div class="form-group">
            <label for="name">Nama Pemasok</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama pemasok" value="{{ $supplier->name }}">
        </div>
        <div class="form-group">
            <label for="phone_number">Nomor Telepon</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" aria-describedby="phone_number"
                placeholder="Masukkan nomor telepon" value="{{ $supplier->phone_number }}">
        </div>
        <div class="form-group">
            <label for="address">Alamat</label>
            <input type="text" class="form-control" id="address" name="address" aria-describedby="address"
                placeholder="Masukkan alamat" value="{{ $supplier->address }}">
        </div>
        <a href="{{ route('supplier.index') }}" class="btn btn-danger">Batal</a>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection
