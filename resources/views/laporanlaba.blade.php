@extends('layouts.btemplate')
@section('content')
    <h4 class="font-weight-bold">Laporan Laba Kotor</h4>
    <h5>{{ $dataToko->name }}</h5>
    <input class="form-control mb-3 border border-primary" id="myInput" type="text" placeholder="Cari..."
        style="max-width: 400px;">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Jumlah Terjual</th>
                <th>HPP</th>
                <th>Pendapatan</th>
                <th>Laba Kotor</th>
            </tr>
        </thead>
        <tbody id="myTable">
            @foreach ($laporan as $item)
                <tr>
                    <td>{{ $item['produk'] }}</td>
                    <td>{{ $item['jumlah_terjual'] }}</td>
                    <td>{{ number_format($item['hpp'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['pendapatan'], 0, ',', '.') }}</td>
                    <td>{{ number_format($item['laba_kotor'], 0, ',', '.') }}</td>
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
        });
    </script>
@endsection
