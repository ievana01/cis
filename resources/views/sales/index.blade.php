@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <a class="btn btn-primary mb-2" href="{{ route('sales.create') }}">+ Order Penjualan</a>
    <table class="table table-hover text-center">
        <thead>
            <tr>
                <th>Nomor Ref</th>
                <th>Tanggal Order</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Jenis Pembayaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $data)
                <tr>
                    <td>{{ $data->sales_invoice }}</td>
                    <td>{{ date('d-m-Y', strtotime($data->date)) }}</td>
                    <td>{{ $data->customer_name ?? $data->custname }}</td>
                    <td>Rp. {{ number_format($data->total_price, 0, ',', '.') }}</td>
                    <td>{{ $data->payment_method_name }}</td>
                    <td>
                        <a href="" class="btn btn-warning">Kirim Produk</a>
                        <a href="{{ route('sales.showNota', $data->id_sales) }}" class="btn btn-info">Tampilkan Nota</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Data tidak tersedia</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
