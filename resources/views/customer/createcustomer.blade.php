@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('customer.store') }}">
        @csrf
        <h4 class="font-weight-bold">Tambah Pelanggan</h4>
        <div class="form-group">
            <label for="name">Nama</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama pelanggan">
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-group">
            <label for="phone_number">Nomor Telepon</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" aria-describedby="phone_number"
                placeholder="Masukkan nomor telepon">
            @error('phone_number')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" class="form-control" id="email" name="email" aria-describedby="email"
                placeholder="Masukkan email">
            @error('email')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-group">
            <label for="address">Alamat</label>
            <input type="text" class="form-control" id="address" name="address" aria-describedby="address"
                placeholder="Masukkan alamat">
            @error('address')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('customer.index') }}" class="btn btn-danger">Batal</a>
    </form>
@endsection
