@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h6>Daftar Pemasok</h6>
    <a class="btn btn-primary mb-2" href="{{ route('supplier.create') }}">+ Tambah Pemasok</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Nama</th>
                <th scope="col">Nomor Telepon</th>
                <th scope="col">Alamat</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($supplier as $data)
                <tr>
                    <td>{{ $data->id_supplier }}</td>
                    <td>{{ $data->name }}</td>
                    <td>{{ $data->phone_number }}</td>
                    <td>{{ $data->address }}</td>
                    <td><a href="{{route('supplier.edit', $data->id_supplier)}}" class="btn btn-warning">Edit</a>
                        <form method="POST" action="{{ route('supplier.destroy', $data->id_supplier) }}"  style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <input type="submit" value="Delete" class="btn btn-danger"
                                onclick="return confirm('Are you sure to delete {{ $data->id_supplier }} - {{ $data->name }} ?');">
                        </form></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Data pemasok belum tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
