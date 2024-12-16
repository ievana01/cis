@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <a class="btn btn-primary mb-2" href="{{ route('purchase.create') }}">+ Purchase Orders</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Purchase Invoice</th>
                <th>Purchase Date</th>
                <th>Supplier</th>
                <th>Total</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchase as $data)
                <tr>
                    <td>{{ $data->purchase_invoice }}</td>
                    <td>{{ $data->date }} </td>
                    <td>{{ $data->supplier_name }}</td>
                    <td>Rp. {{ $data->total_purchase }}</td>
                    <td>
                        @if ($data->payment_method != null)
                            Payment Success
                        @else
                            Payment not Registered
                        @if ($payProd->id_detail_configuration == 16 && $data->expected_arrival >= now()->toDateString())
                                <a href="#modalPayment" class="btn btn-warning" data-toggle="modal"
                                    onclick="paymentForm({{ $data->id_purchase }})">Payment</a>
                            @endif
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Data not available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="modal fade" id="modalPayment" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Payment Register</h5>
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
