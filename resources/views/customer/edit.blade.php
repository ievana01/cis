@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('customer.update', $customer->id_customer) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="id_customer">ID Customer</label>
            <input class="form-control" type="text" name="id_customer" id="id_customer" value="{{$customer->id_customer}}" disabled>
        </div>
        <div class="form-group">
            <label for="name">Nama</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama customer" value="{{$customer->name}}" >
        </div>
        <div class="form-group">
            <label for="phone_number">Nomor Telepon</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" aria-describedby="phone_number"
                placeholder="Masukkan nomor telepon" value="{{$customer->phone_number}}" >
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" class="form-control" id="email" name="email" aria-describedby="email"
                placeholder="Masukkan email" value="{{$customer->email}}" >
        </div>
        <div class="form-group">
            <label for="address">Alamat</label>
            <input type="text" class="form-control" id="address" name="address" aria-describedby="address"
                placeholder="Masukkan alamat" value="{{$customer->address}}" >
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection
