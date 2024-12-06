@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h6>Daftar Pelanggan</h6>
    <a class="btn btn-primary mb-2" href="{{ route('customer.create') }}">+ Tambah Pelanggan</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Nama</th>
                <th scope="col">Email</th>
                <th scope="col">Nomor Telepon</th>
                <th scope="col">Alamat</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customer as $data)
                <tr>
                    <td>{{ $data->id_customer }}</td>
                    <td>{{ $data->name }}</td>
                    <td>{{ $data->email }}</td>
                    <td>{{ $data->phone_number }}</td>
                    <td>{{ $data->address }}</td>
                    <td><a href="{{ route('customer.edit', $data->id_customer) }}" class="btn btn-warning">Edit</a>
                        <form method="POST" action="{{ route('customer.destroy', $data->id_customer) }}"
                            style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <input type="submit" value="Delete" class="btn btn-danger"
                                onclick="return confirm('Are you sure to delete {{ $data->id_customer }} - {{ $data->name }} ?');">
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Data belum tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
