@extends('layouts.blank')
@section('content')
    <form method="POST" action="{{ route('customer.store') }}">
        @csrf
        <h4>New Customer</h4>
        <div class="form-group">
            <label for="name">Nama</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama customer">
        </div>
        <div class="form-group">
            <label for="phone_number">Nomor Telepon</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" aria-describedby="phone_number"
                placeholder="Masukkan nomor telepon">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" class="form-control" id="email" name="email" aria-describedby="email"
                placeholder="Masukkan email">
        </div>
        <div class="form-group">
            <label for="address">Alamat</label>
            <input type="text" class="form-control" id="address" name="address" aria-describedby="address"
                placeholder="Masukkan alamat">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection
