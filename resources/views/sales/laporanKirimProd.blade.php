@extends('layouts.btemplate')
@section('content')
    <h4 class="font-weight-bold">Laporan Pengiriman Produk</h4>
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="startDate" class="font-weight-bold">Tanggal Awal:</label>
            <input type="date" id="startDate" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="endDate" class="font-weight-bold">Tanggal Akhir:</label>
            <input type="date" id="endDate" class="form-control">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button id="filterButton" class="btn btn-info"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>
    </div>

    <input class="form-control mb-3 border border-primary" id="myInput" type="text" placeholder="Cari..."
        style="max-width: 400px;">

    <div class="table-responsive">
        <table class="table table-striped table-hover text-center">
            <thead class="thead-dark">
                <tr>
                    <th>No Ref</th>
                    <th>Tanggal Kirim</th>
                    <th>Nama Produk</th>
                    <th>Penjualan</th>
                    <th>Dikirim</th>
                    <th>Belum Dikirim</th>
                </tr>
            </thead>
            <tbody id="myTable">
                @forelse ($data as $deliveryId => $items)
                    {{-- Iterasi pertama (group by delivery_id) --}}
                    @foreach ($items as $index => $dt)
                        {{-- Iterasi kedua untuk detail produk --}}
                        <tr>
                            @if ($index === 0)
                                <td rowspan="{{ count($items) }}">{{ $dt->invoice }}</td> {{-- Menampilkan Invoice sekali untuk setiap pengiriman --}}
                                <td class="order-date" rowspan="{{ count($items) }}">
                                    {{ date('d-m-Y', strtotime($dt->tanggal_kirim)) }}</td>
                            @endif
                            <td>{{ $dt->prod_name }}</td>
                            <td>{{ $dt->jumlah_beli }}</td>
                            <td>{{ $dt->jumlah_kirim }}</td>
                            <td>{{ $dt->jumlah_beli - $dt->jumlah_kirim }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Data tidak tersedia</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
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

            // Fungsi filter berdasarkan rentang tanggal
            $("#filterButton").on("click", function() {
                var startDate = new Date($("#startDate").val());
                var endDate = new Date($("#endDate").val());

                $("#myTable tr").each(function() {
                    var orderDateText = $(this).find(".order-date").text().trim();
                    var orderDate = new Date(orderDateText.split("-").reverse().join(
                        "-")); // Convert ke format YYYY-MM-DD

                    if (orderDate >= startDate && orderDate <= endDate) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    </script>
@endsection
