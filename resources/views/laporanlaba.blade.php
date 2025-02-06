@extends('layouts.btemplate')
@section('content')
    <h4 class="font-weight-bold">Laporan Laba Kotor</h4>
    <h5>{{ $dataToko->name }}</h5>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Jumlah Terjual</th>
                <th>Harga Jual</th>
                <th>HPP</th>
                <th>Pendapatan</th>
                <th>Laba Kotor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($laporan as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['jumlahTerjual'] }}</td>
                    <td>{{ number_format($item['hargaJual'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['hpp'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['pendapatan'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['labaKotor'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
