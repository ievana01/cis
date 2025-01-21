@extends('layouts.btemplate')
@section('content')

    <div class="row">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">TOTAL PENJUALAN HARI INI</h5>
                    <p class="card-text">Rp. {{ $sales }}</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">TOTAL PEMBELIAN HARI INI</h5>
                    <p class="card-text">Rp. {{ $purchase }}</p>
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
