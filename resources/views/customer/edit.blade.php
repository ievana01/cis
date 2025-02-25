@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('customer.update', $customer->id_customer) }}">
        @csrf
        @method('PUT')
        <h4 class="font-weight-bold">Edit Pelanggan</h4>
        <div class="form-group">
            <label for="id_customer">ID Pelanggan</label>
            <input class="form-control" type="text" name="id_customer" id="id_customer" value="{{ $customer->id_customer }}"
                disabled>
        </div>
        <div class="form-group">
            <label for="name">Nama</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama customer" value="{{ $customer->name }}">
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-group">
            <label for="phone_number">Nomor Telepon</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" aria-describedby="phone_number"
                placeholder="Masukkan nomor telepon" value="{{ $customer->phone_number }}">
            @error('phone_number')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" class="form-control" id="email" name="email" aria-describedby="email"
                placeholder="Masukkan email" value="{{ $customer->email }}">
            @error('email')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-group">
            <label for="address">Alamat</label>
            <input type="text" class="form-control" id="address" name="address" aria-describedby="address"
                placeholder="Masukkan alamat" value="{{ $customer->address }}">
            @error('address')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('customer.index') }}" class="btn btn-danger">Batal</a>
    </form>
@endsection
