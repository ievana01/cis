@extends('layouts.btemplate')
@section('content')
    <h4 class="font-weight-bold mb-4">Laporan Pembelian Produk</h4>
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
                    <th>Staf</th>
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
                        <td>{{ $dp->emp_name }}</td>
                        <td>{{ $dp->product_name }}</td>
                        <td class="order-date">{{ date('d-m-Y', strtotime($dp->date)) }}</td>
                        <td>{{ $dp->supplier_name }}</td>
                        <td>{{ $dp->total_quantity_product }}</td>
                        <td>Rp. {{ number_format($dp->total_purchase, 0, ',', '.') }}</td>
                        <td>{{ $dp->payment_method ?? 'Belum terdaftar' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-danger">Data tidak tersedia</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            // Pencarian teks di dalam tabel
            $("#myInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#myTable tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Filter berdasarkan rentang tanggal
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
