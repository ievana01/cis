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
                <th>ID</th>
                <th>Category</th>
                @if ($catProd->id_detail_configuration == 17)
                    <th>List Sub Category</th>
                @endif
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($category as $data)
                <tr>
                    <td>{{ $data->id_category }}</td>
                    <td>{{ $data->code_category }} - {{ $data->name }}</td>
                    <td>
                        @forelse ($subCategory->where('category_id', $data->id_category) as $sub)
                            <ul>
                                <li>{{ $sub->code_sub_category }} - {{ $sub->name }}</li>
                            </ul>
                        @empty
                            <span>Data not available</span>
                        @endforelse
                    </td>
                    <td>
                        <a href="#modalAddSub" class="btn btn-info btn-sm" data-toggle="modal"
                            onclick="formSubCategory({{ $data->id_category }})">+Sub Category</a>

                        <a href="{{ route('category.edit', $data->id_category) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form method="POST" action="{{ route('category.destroy', $data->id_category) }}"
                            style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <input type="submit" value="Delete" class="btn btn-danger btn-sm"
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
    <div class="modal fade" id="modalAddSub" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Add Sub Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalContent">
                </div>
            </div>
        </div>
    </div>
@endsection


@section('javascript')
    <script>
        function formSubCategory(id_category) {
            $.ajax({
                type: 'POST',
                url: '{{ route('category.formSubCategory') }}',
                data: {
                    '_token': '<?php echo csrf_token(); ?>',
                    'id': id_category
                },
                success: function(data) {
                    $('#modalContent').html(data.msg)
                }
            });
        }
    </script>
@endsection
