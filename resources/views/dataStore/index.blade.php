@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Data Toko</h4>
            @if ($data != null)
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Logo:
                        <img src="{{ asset('storage/' . $data->logo) }}" alt="Logo" width="50" height="50" />
                    </li>
                    <li class="list-group-item">Nama: {{ $data->name }}</li>
                    <li class="list-group-item">Alamat: {{ $data->address }}</li>
                    <li class="list-group-item">Email: {{ $data->email }}</li>
                    <li class="list-group-item">Narahubung: {{ $data->contact_person }}</li>
                    <li class="list-group-item">Nomor Telepon: {{ $data->phone_number }}</li>
                    <li class="list-group-item"><a href="{{ route('dataStore.edit', $data->id_store) }}"
                            class="btn btn-warning">Edit</a></li>
                </ul>
            @else
                <a href="{{ route('dataStore.create') }}" class="btn btn-primary">+ Data Toko</a>
            @endif
        </div>
    </div>
@endsection
