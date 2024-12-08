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
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchase as $data)
                <tr>
                    <td>{{ $data->purchase_invoice }}</td>
                    <td>{{ $data->date }}</td>
                    <td>{{ $data->supplier_name }}</td>
                    <td>{{ $data->total_purchase }}</td>
                    <td>{{ $data->payment_method_name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Data not available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
