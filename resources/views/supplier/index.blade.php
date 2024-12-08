@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">List Supplier</h4>
    <a class="btn btn-primary mb-2" href="{{ route('supplier.create') }}">+ Add Supplier</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone Number</th>
                <th>Address</th>
                <th>Action</th>
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
                    <td colspan="4" class="text-center">Data not available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
