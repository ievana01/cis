@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <a class="btn btn-primary mb-2" href="{{ route('sales.create') }}">+ Tambah Nota</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">Nomor Nota</th>
                <th scope="col">Tanggal Dibuat</th>
                <th scope="col">Pelanggan</th>
                <th scope="col">Total</th>
                {{-- <th>Status</th> --}}
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $data)
                <tr>
                    <td>{{ $data->sales_invoice }}</td>
                    <td>{{ $data->date }}</td>
                    <td>{{ $data->customer->name}}</td>
                    <td>Rp. {{ $data->total_price }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Data belum tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
