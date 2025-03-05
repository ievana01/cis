@extends('layouts.btemplate')
@section('content')
    <h4 class="font-weight-bold">Laporan Pembelian Produk</h4>
    <input class="form-control" id="myInput" type="text" placeholder="Cari..">
    <table class="table table-border text-center">
        <thead>
            <tr>
                <th>No Ref</th>
                <th>Nama Produk</th>
                <th>Tanggal Order</th>
                <th>Pemasok</th>
                <th>Jumlah Pembelian</th>
                <th>Total</th>
                <th>Jenis Pembayaran</th>
            </tr>
        </thead>
        <tbody id="myTable">
            @forelse ($data as $dp)
                <tr>
                    <td>{{ $dp->purchase_invoice }}</td>
                    <td>{{ $dp->product_name }}</td>
                    <td>{{ date('d-m-Y', strtotime($dp->date)) }}</td>
                    <td>{{ $dp->supplier_name }}</td>
                    <td>{{ $dp->total_quantity_product }}</td>
                    <td>Rp. {{ number_format($dp->total_purchase, 0, ',', '.') }}</td>
                    <td>{{ $dp->payment_method ?? 'Belum terdaftar' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Data tidak tersedia</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            $("#myInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#myTable tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
@endsection
