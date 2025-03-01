@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">Pindah Produk</h4>
    <a class="btn btn-primary mb-2" href="{{ route('pindahProduk.create') }}">+ Pindah Produk</a>
    @forelse ($pindah as $p)
        <div class="card mb-2">
            <div class="card-body">
                <h5 class="card-title">Pindah Produk</h5>
                <div class="d-flex">
                    <p class="card-text">Dari Gudang : {{ $p->warehouse_in_name }}</p>
                    <p class="card-text">Ke Gudang : {{ $p->warehouse_out_name }}</p>
                </div>
                <label for="">Daftar produk yang dipindah:</label>
                <ol class="list-group list-group-flush">
                    <li class="list-group-item"> {{$p->product_name}} - {{$p->quantity}}</li>
                </ol>
            </div>
        </div>
    @empty
    <p>DATA TIDAK TERSEDIA</p>
    @endforelse
@endsection
