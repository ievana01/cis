@extends('layouts.btemplate')
@section('content')
    <h4 class="font-weight-bold">Data Sales</h4>
    <table class="table table-border">
        <thead>
            <tr>
                <th>Sales Invoice</th>
                <th>Product Name</th>
                <th>Stock Product Sold</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $data)
                <tr>
                    <td>{{$data->sales_invoice}}</td>
                    <td>{{$data->product_name}}</td>
                    <td>{{$data->stock}}</td>
                </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center">Data not available.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection