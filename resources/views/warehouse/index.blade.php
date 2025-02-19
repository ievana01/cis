@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">Daftar Gudang</h4>
    @if ($multiWh)
        <a class="btn btn-primary mb-2" href="{{ route('warehouse.create') }}">+ Tambah Gudang</a>
    @elseif($warehouse->isEmpty())
        <a class="btn btn-primary mb-2" href="{{ route('warehouse.create') }}">+ Tambah Gudang</a>
    @endif
    <table class="table table-hover">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Gudang</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($warehouse as $data)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $data->name }}</td>
                    <td>{{ $data->address }}</td>
                    <td>
                        <a href="{{ route('warehouse.edit', $data->id_warehouse) }}" class="btn btn-warning btn-sm">Edit</a>
                        @if ($multiWh != null)
                            <form method="POST" action="{{ route('warehouse.destroy', $data->id_warehouse) }}"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <input type="submit" value="Hapus" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure to delete {{ $data->id_warehouse }} - {{ $data->name }} ?');">
                            </form>
                        @endif
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Data tidak tersedia</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
