@extends('layouts.blank')

@section('content')
    <div class="container mt-4">
        <!-- Informasi Toko -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <img src="{{ asset('storage/' . $dataToko->logo) }}" alt="Logo {{ $dataToko->name }}"
                    style="width: 50px; height: 50px; margin-right: 15px;">
                <h5 class="font-weight-bold">{{ $dataToko->name }}</h5>
            </div>
            <div>
                <p>{{ $dataToko->address }}</p>
                <p>Email: {{ $dataToko->email }}</p>
                <p>Kontak: {{ $dataToko->contact_person }} ({{ $dataToko->phone_number }})</p>
            </div>
        </div>
        <hr style="border: 1px solid black; margin-top: 5px;">



        <!-- Judul Nota Penjualan -->
        <div class="text-center mb-4">
            <h4 class="font-weight-bold">Nota Penjualan</h4>
        </div>

        <!-- Informasi Penjualan -->
        <div class="mb-4">
            <p><strong>Nomor Ref:</strong> {{ $sales->sales_invoice }}</p>
            <p><strong>Tanggal Order:</strong> {{ date('d-m-Y', strtotime($sales->date)) }}</p>
            <p><strong>Pelanggan:</strong> {{ $sales->customer_name_by_id ?? $sales->customer_name }}</p>
            <p><strong>Staf:</strong> {{ $sales->e_name }}</p>
        </div>

        <!-- Daftar Produk -->
        <h5 class="font-weight-bold">Daftar Produk</h5>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>No.</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Total Harga (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($salesDetail as $detail)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $detail->product_name }}</td>
                        <td>{{ $detail->total_quantity }}</td>
                        <td>{{ number_format($detail->total_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td>-</td>
                    <td>Pajak</td>
                    <td>{{ $sales->tax }}%</td>
                    <td>{{ number_format(($sales->tax / 100) * $detail->total_price, 0, ',', '.') }}</td>

                </tr>
                <tr>
                    <td>-</td>
                    <td>Diskon</td>
                    <td>-</td>
                    <td>{{ number_format($sales->discount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>-</td>
                    <td>Biaya Pengiriman</td>
                    <td>-</td>
                    <td>{{ number_format($sales->shipping_cost, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Total Pembayaran -->
        <div class="text-right mt-3">
            <h5><strong>Total Pembayaran: Rp. {{ number_format($sales->total_price, 0, ',', '.') }}</strong></h5>
        </div>

        <!-- Tombol Tutup -->
        <div class="text-right mt-4">
            <a href="{{ route('sales.index') }}" class="btn btn-danger">Tutup</a>
        </div>
    </div>
@endsection
