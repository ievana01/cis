@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">List Warehouse</h4>
    <a class="btn btn-primary mb-2" href="{{ route('warehouse.create') }}">+ Add Warehouse</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Warehouse Name</th>
                <th>Address</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($warehouse as $data)
                <tr>
                    <td>{{ $data->id_warehouse }}</td>
                    <td>{{ $data->name }}</td>
                    <td>{{ $data->address }}</td>
                    <td>
                        <a href="#modalEditA" class="btn btn-warning" data-toggle="modal"
                            onclick="getEditForm({{ $data->id_warehouse }})">Edit</a>
                        <form method="POST" action="{{ route('warehouse.destroy', $data->id_warehouse) }}"
                            style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <input type="submit" value="Delete" class="btn btn-danger"
                                onclick="return confirm('Are you sure to delete {{ $data->id_warehouse }} - {{ $data->name }} ?');">
                        </form>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">Data not available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="modal fade" id="modalEditA" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-wide">
            <div class="modal-content">
                <div class="modal-body" id="modalContent">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        function getEditForm(id_warehouse) {
            $.ajax({
                type: 'POST',
                url: '{{ route('warehouse.getEditForm') }}',
                data: {
                    '_token': '<?php echo csrf_token(); ?>',
                    'id': id_warehouse
                },
                success: function(data) {
                    $('#modalContent').html(data.msg)
                }
            });
        }
    </script>
@endsection
