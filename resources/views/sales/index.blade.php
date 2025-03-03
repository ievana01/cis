@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <a class="btn btn-primary mb-2" href="{{ route('sales.create') }}">+ Order Penjualan</a>
    <input class="form-control" id="myInput" type="text" placeholder="Cari..">
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
        <tbody id="myTable">
            @forelse ($sales as $data)
                <tr>
                    <td>{{ $data->sales_invoice }}</td>
                    <td>{{ date('d-m-Y', strtotime($data->date)) }}</td>
                    <td>{{ $data->customer_name ?? $data->custname }}</td>
                    <td>Rp. {{ number_format($data->total_price, 0, ',', '.') }}</td>
                    <td>{{ $data->payment_method_name }}</td>
                    <td class="d-flex justify-content-center">
                        @if ($data->delivery_date != null)
                            <a href="{{ route('sales.showProd', $data->id_sales) }}"
                                class="btn btn-warning btn-sm mr-2">Kirim
                                Produk</a>
                        @endif
                        <a href="{{ route('sales.showNota', $data->id_sales) }}" class="btn btn-info btn-sm">Tampilkan
                            Nota</a>
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
