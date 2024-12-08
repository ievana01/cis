@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">List Customer</h4>
    <a class="btn btn-primary mb-2" href="{{ route('customer.create') }}">+ Add Customer</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Address</th>
                <th>Action</th>
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
                    <td colspan="5" class="text-center">Data not available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
