@extends('layouts.btemplate')
@section('content')
    <h4 class="font-weight-bold">Lokasi Produk</h4>
    <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Product</th>
                <th>Stok</th>
                <th>Lokasi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($product as $p)
                <tr>
                    <th>{{ $loop->iteration }}</th>
                    <th>{{ $p->product_name }}</th>
                    <th>{{ $p->stock }}</th>
                    <th>{{ $p->warehouse_name }}</th>
                    <th><a href="{{ route('productMove.edit', $p->id_product) }}" class="btn btn-warning">Pindah Produk</a>
                    </th>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
