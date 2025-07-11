@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">Daftar Produk</h4>
    <a class="btn btn-primary mb-2" href="{{ route('product.create') }}">+ Tambah Produk</a>
    <div class="row">
        @forelse ($product as $data)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                <div class="card" style="width: 100%;">
                    @if ($data->images->count() > 0)
                        <img class="card-img-top" src="{{ asset('storage/' . $data->images->first()->file_image) }}"
                            alt="Card image cap">
                    @else
                        <img class="card-img-top"
                            src="https://static.vecteezy.com/system/resources/previews/004/141/669/non_2x/no-photo-or-blank-image-icon-loading-images-or-missing-image-mark-image-not-available-or-image-coming-soon-sign-simple-nature-silhouette-in-frame-isolated-illustration-vector.jpg"
                            alt="No image available">
                    @endif
                    <div class="card-body">

                        <p class="card-text">{{ $data->name }}</p>
                        <p class="card-text">Harga: Rp.{{ number_format($data->price, 0, ',', '.') }}</p>
                        @if ($data->total_stock <= $data->min_total_stock)
                            <p class="card-text">Total stok: {{ $data->total_stock }} {{ $data->unit }} <span class="text-danger">[STOK KURANG!]</span></p>
                        @else
                            <p class="card-text">Total stok: {{ $data->total_stock }} {{ $data->unit }}</p>
                        @endif
                        <a href="{{ route('product.edit', $data->id_product) }}" class="btn btn-warning">Edit</a>
                        <form method="POST" action="{{ route('product.destroy', $data->id_product) }}"
                            style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <input type="submit" value="Hapus" class="btn btn-danger"
                                onclick="return confirm('Apakah anda yaking menghapus {{ $data->id_product }} - {{ $data->name }} ?');">
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="ml-2 pt-2">
                <p>Data tidak tersedia</p>
            </div>
        @endforelse
    </div>
@endsection
