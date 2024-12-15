@extends('layouts.btemplate')
@section('content')

    <div class="row">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">TOTAL SALES TODAY</h5>
                    <p class="card-text">Rp. {{ $sales }}</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">TOTAL PURCHASE TODAY</h5>
                    <p class="card-text">Rp. {{ $purchase }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">TOTAL DATA PRODUCT </h5>
                    <p class="card-text">{{ $product }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
