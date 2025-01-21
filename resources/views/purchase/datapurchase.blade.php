@extends('layouts.btemplate')
@section('content')
    <h4 class="font-weight-bold">Data Pembelian Produk</h4>
    <input class="form-control" id="myInput" type="text" placeholder="Cari..">
    <table class="table table-border text-center">
        <thead>
            <tr>
                <th>No Ref</th>
                <th>Nama Produk</th>
                <th>Tanggal Order</th>
                <th>Jumlah Pembelian</th>
            </tr>
        </thead>
        <tbody id="myTable">
            @forelse ($data as $dp)
                <tr>
                    <td>{{ $dp->purchase_invoice }}</td>
                    <td>{{ $dp->product_name }}</td>
                    <td>{{ date('d-m-Y', strtotime($dp->date)) }}</td>
                    <td>{{ $dp->move_stock }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">Data tidak tersedia</td>
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
