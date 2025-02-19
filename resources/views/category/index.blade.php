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
                    <td >
                        @if ($catProd && $catProd->id_detail_configuration == 23)
                            <ul class="list-unstyled">
                                @foreach ($subCategory->where('category_id', $data->id_category) as $sub)
                                    <li class="d-flex justify-content-between align-items-center mt-1">
                                        <span>{{ $sub->code_sub_category }} - {{ $sub->name }}</span>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('subCategory.edit', $sub->id_sub_category) }}"
                                                class="btn btn-warning btn-sm mr-1">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <form method="POST"
                                                action="{{ route('subCategory.destroy', $sub->id_sub_category) }}"
                                                onsubmit="return confirm('Apakah anda yakin menghapus {{ $sub->id_sub_category }} - {{ $sub->name }} ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td>
                        @if ($catProd && $catProd->id_detail_configuration != null)
                            {{-- <a href="#modalAddSub" class="btn btn-info btn-sm" data-toggle="modal"
                                onclick="formSubCategory({{ $data->id_category }})">+Sub Kategori</a> --}}
                            <a href="{{ route('subCategory.create', $data->id_category) }}" class="btn btn-info btn-sm">+
                                Sub Kategori</a>
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
                    <td colspan="4" class="text-center">Data tidak tersedia</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
