@extends('layouts.btemplate')
@section('content')
    <input class="form-control mb-3 border border-primary" id="myInput" type="text" placeholder="Cari..."
        style="max-width: 400px;">
    <table class="table table-stripped table-hover">
        <thead>
            <tr>
                <th>Lokasi Penyimpanan</th>
                <th>Nama Produk</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody id="myTable">
            @foreach ($product as $p)
                <tr>
                    <td>{{ $p->warehouse_name }}</td>
                    <td>{{ $p->product_name }}</td>
                    <td>{{ $p->stok }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            // Fungsi pencarian teks di dalam tabel
            $("#myInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();

                $("#myTable tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        })
    </script>
@endsection
