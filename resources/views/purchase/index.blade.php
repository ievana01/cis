@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <a class="btn btn-primary mb-2" href="{{ route('purchase.create') }}">+ Order Pembelian</a>
    <table class="table table-hover text-center">
        <thead>
            <tr>
                <th>Nomor Ref</th>
                <th>Tanggal Pembelian</th>
                <th>Pemasok</th>
                <th>Total</th>
                <th>Status Pembayaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchase as $data)
                <tr>
                    <td>{{ $data->purchase_invoice }}</td>
                    <td>{{ date('d-m-Y', strtotime($data->date)) }}</td>
                    <td>{{ $data->supplier_name }}</td>
                    <td>Rp. {{ number_format($data->total_purchase, 0, ',', '.') }}</td>

                    <td>
                        @if ($data->payment_method != null)
                            <label>Pembayaran Sukses</label>
                        @else
                            <label class="text-danger">Pembayaran blm terdaftar</label>
                            @if ($payProd->id_detail_configuration == 21 && $data->expected_arrival <= now()->toDateString())
                                <br>
                                <a href="#modalPayment" class="btn btn-success btn-sm" data-toggle="modal"
                                    onclick="paymentForm({{ $data->id_purchase }})">Bayar</a>
                            @endif
                        @endif
                    </td>
                    <td class="d-flex justify-content-center">
                        <a href="{{route('purchase.showProd', $data->id_purchase)}}" class="btn btn-warning btn-sm mr-2">Terima Produk</a>
                        <a href="{{ route('purchase.showNota', $data->id_purchase) }}" class="btn btn-info btn-sm">Tampilkan
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
    <div class="modal fade" id="modalPayment" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Registrasi Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalContent">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        function paymentForm(id_purchase) {
            $.ajax({
                type: 'POST',
                url: '{{ route('purchase.paymentForm') }}',
                data: {
                    '_token': '<?php echo csrf_token(); ?>',
                    'id': id_purchase
                },
                success: function(data) {
                    $('#modalContent').html(data.msg)
                }
            });
        }
    </script>
@endsection
