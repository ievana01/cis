@extends('layouts.btemplate')
@section('content')
    <table class="table table-stripped">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Produk</th>
                <th>Stok</th>
                <th>Lokasi Penyimpanan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($product as $p)
                <tr>
                    <td>{{ $loop->iteration }}.</td>
                    <td>{{$p->name}}</td>
                    <td>{{$p->total_stock}}</td>
                    <td>{{$p->warehouse_name}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
