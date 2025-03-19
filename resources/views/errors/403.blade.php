@extends('layouts.btemplate')

@section('content')
    <div class="container d-flex justify-content-center align-items-center pt-2">
        <div class="card shadow-lg">
            <div class="card-body text-center">
                <h5 class="card-title text-danger">⚠️Akses Ditolak⚠️</h5>
                <p class="card-text">Halaman ini hanya bisa diakses oleh pemilik toko!</p>
                <a href="{{ route('home') }}" class="btn btn-primary">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
@endsection
