@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">Pindah Produk</h4>
    @if ($multiWh == null)
        <div class="card">
            <div class="card-body text-danger">
                Konfigurasi multi gudang tidak aktif. <br>Hubungi pemilik toko untuk mengaktifkan konfigurasi
                ini!
            </div>
        </div>
    @else
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Produk</th>
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
                        <th><a href="{{ route('productMove.edit', $p->id_product) }}" class="btn btn-warning">Pindah
                                Produk</a>
                        </th>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
