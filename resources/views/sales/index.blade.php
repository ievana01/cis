@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <a class="btn btn-primary mb-2" href="{{ route('sales.create') }}">+ Sales Orders</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Sales Invoice</th>
                <th>Order Date</th>
                <th>Customer</th>
                <th>Total Price</th>
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $data)
                <tr>
                    <td>{{ $data->sales_invoice }}</td>
                    <td>{{ $data->date }}</td>
                    <td>{{ $data->customer_name }}</td>
                    <td>Rp. {{ $data->total_price }}</td>
                    <td>{{ $data->payment_method_name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Data not available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
