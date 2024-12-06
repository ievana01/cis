@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <a class="btn btn-primary mb-2" href="{{ route('purchase.create') }}">+ Tambah Nota</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">Nomor Nota</th>
                <th scope="col">Tanggal Dibuat</th>
                <th scope="col">Supplier</th>
                <th scope="col">Total</th>
                {{-- <th>Status</th> --}}
            </tr>
        </thead>
        <tbody>
            @forelse ($purchase as $data)
                <tr>
                    <td>{{ $data->purchase_invoice }}</td>
                    <td>{{ $data->date }}</td>
                    <td>{{ $data->supplier->name}}</td>
                    <td>{{ $data->total_purchase }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Data belum tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
