@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">Daftar Pindah Produk</h4>
    @if ($multiWh == null)
        <div class="card">
            <div class="card-body text-danger">
                Konfigurasi multi gudang tidak aktif. <br>Hubungi pemilik toko untuk mengaktifkan konfigurasi
                ini!
            </div>
        </div>
    @else
        <a class="btn btn-primary mb-2" href="{{ route('pindahProduk.create') }}">+ Pindah Produk</a>
        @forelse ($pindah as $p)
            <div class="card mb-2">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa-solid fa-exchange-alt text-primary"></i> Pindah Produk</h5>
                    <p>Nomor Ref: {{$p->invoice_number}}</p>
                    <p>Tanggal Pindah: {{ date('d-m-Y', strtotime($p->date)) }}</p>
                    <div class="d-flex mr-6 mb-2">
                        <i class="fa-solid fa-warehouse text-danger"></i>
                        <p class="mb-2"><strong>Dari Gudang:</strong> {{ $p->w_in }}</p>
                    </div>
                    <div class="d-flex">
                        <i class="fa-solid fa-warehouse text-success"></i>
                        <p class="mb-0"><strong>Ke Gudang:</strong> {{ $p->w_out }}</p>
                    </div>

                    <br>
                    <label class="font-weight-bold">Daftar produk yang dipindah:</label>
                    <table class="table table-striped text-center">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Produk</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($p->products as $prod)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $prod['prod_name'] }}</td>
                                    <td>{{ $prod['quantity'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <p>DATA TIDAK TERSEDIA</p>
        @endforelse
    @endif
@endsection
