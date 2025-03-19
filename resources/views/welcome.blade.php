@extends('layouts.btemplate')
@section('content')
    <div class="card text-center">
        <div class="card-header">
            <h5>Shortcut Menu</h5>
            <div class="d-grid gap-3" style="grid-template-columns: repeat(3, 1fr);">
                <a class="btn btn-primary" href="{{ route('sales.create') }}">+ Order Penjualan</a>
                <a class="btn btn-success" href="{{ route('purchase.create') }}">+ Order Pembelian</a>
                <a class="btn btn-info" href="{{ route('product.index') }}">Daftar Produk</a>
                @if (Auth::check() && Auth::user()->role_id == 1)
                    <a class="btn btn-warning" href="{{ route('register') }}">+ Akun Staf</a>
                @endif

            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">TOTAL PENJUALAN HARI INI</h5>
                    <p class="card-text">Rp. {{ number_format($sales, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">TOTAL PEMBELIAN HARI INI</h5>
                    <p class="card-text">Rp. {{ number_format($purchase, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">JUMLAH PRODUK </h5>
                    <p class="card-text">{{ $product }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
