@extends('layouts.btemplate')
@section('content')
    <table class="table table-stripped">
        <thead>
            <tr>
                <th>ID Product</th>
                <th>Name</th>
                <th>Stock</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($product as $p)
                <tr>
                    <td>{{ $p->id_product }}</td>
                    <td>{{$p->name}}</td>
                    <td>{{$p->total_stock}}</td>
                    <td>{{$p->warehouse_name}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
