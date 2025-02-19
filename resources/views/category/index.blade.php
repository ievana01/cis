@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">Daftar Kategori Produk</h4>
    <a class="btn btn-primary mb-2" href="{{ route('category.create') }}">+ Tambah Kategori</a>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Kategori</th>
                @if ($catProd && $catProd->id_detail_configuration == 23)
                    <th>Daftar Sub Kategori</th>
                @endif
                <th>Aksi Kategori</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($category as $data)
                <tr>
                    <td>{{ $loop->iteration }}.</td>
                    <td>{{ $data->code_category }} - {{ $data->name }}</td>
                    @if ($catProd && $catProd->id_detail_configuration == 23)
                        <td>
                            @forelse ($subCategory->where('category_id', $data->id_category) as $sub)
                                <ul>
                                    <li>{{ $sub->code_sub_category }} - {{ $sub->name }} <a href=""
                                            class="btn btn-warning btn-sm">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    </li>
                                </ul>
                            @empty
                                <span>Data tidak tersedia</span>
                            @endforelse
                        </td>
                    @endif
                    <td>
                        @if ($catProd && $catProd->id_detail_configuration != null)
                            <a href="#modalAddSub" class="btn btn-info btn-sm" data-toggle="modal"
                                onclick="formSubCategory({{ $data->id_category }})">+Sub Kategori</a>
                        @endif
                        <a href="{{ route('category.edit', $data->id_category) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form method="POST" action="{{ route('category.destroy', $data->id_category) }}"
                            style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <input type="submit" value="Hapus" class="btn btn-danger btn-sm"
                                onclick="return confirm('Apakah anda yaking menghapus {{ $data->id_category }} - {{ $data->name }} ?');">
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Data not available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="modal fade" id="modalAddSub" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Tambah Sub Kategori</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalContent">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditSub" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Edit Sub Kategori</h5>
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

        function formEditSubCategory(id_sub_category) {
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
