@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">List Category</h4>
    <a class="btn btn-primary mb-2" href="{{ route('category.create') }}">+ Add Category</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Category Name</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($category as $data)
                <tr>
                    <td>{{ $data->id_category }}</td>
                    <td>{{ $data->name }}</td>
                    <td><a href="{{ route('category.edit', $data->id_category) }}" class="btn btn-warning">Edit</a>
                        <form method="POST" action="{{ route('category.destroy', $data->id_category) }}"  style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <input type="submit" value="Delete" class="btn btn-danger"
                                onclick="return confirm('Are you sure to delete {{ $data->id_category }} - {{ $data->name }} ?');">
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center">Data not available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
