@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">Daftar Pelanggan</h4>
    <a class="btn btn-primary mb-2" href="{{ route('customer.create') }}">+ Tambah Pelanggan</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Nomor Telepon</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customer as $data)
                <tr>
                    <td>{{ $loop->iteration }}.</td>
                    <td>{{ $data->name }}</td>
                    <td>{{ $data->email }}</td>
                    <td>{{ $data->phone_number }}</td>
                    <td>{{ $data->address }}</td>
                    <td><a href="{{ route('customer.edit', $data->id_customer) }}" class="btn btn-warning">Edit</a>
                        <form method="POST" action="{{ route('customer.destroy', $data->id_customer) }}"
                            style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <input type="submit" value="Hapus" class="btn btn-danger"
                                onclick="return confirm('Apakah anda yakin menghapus{{ $data->id_customer }} - {{ $data->name }} ?');">
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Data tidak tersedia</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
