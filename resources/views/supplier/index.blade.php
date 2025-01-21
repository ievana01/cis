@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">Daftar Pemasok</h4>
    <a class="btn btn-primary mb-2" href="{{ route('supplier.create') }}">+ Tambah Pemasok</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama</th>
                <th>Nomor Telepon</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($supplier as $data)
                <tr>
                    <td>{{ $loop->iteration }}.</td>
                    <td>{{ $data->name }}</td>
                    <td>{{ $data->phone_number }}</td>
                    <td>{{ $data->address }}</td>
                    <td><a href="{{route('supplier.edit', $data->id_supplier)}}" class="btn btn-warning">Edit</a>
                        <form method="POST" action="{{ route('supplier.destroy', $data->id_supplier) }}"  style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <input type="submit" value="Hapus" class="btn btn-danger"
                                onclick="return confirm('Anda yakin menghapus {{ $data->id_supplier }} - {{ $data->name }} ?');">
                        </form></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Data tidak tersedia</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
