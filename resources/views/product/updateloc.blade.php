@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('productMove.update', $product->id_product) }}">
        @csrf
        @method('PUT')
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Jumlah Produk</th>
                    <th>Lokasi Produk Saat ini</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>{{ $product->name }}</th>
                    <th>{{ $product->total_stock }}</th>
                    <th>{{ $product->warehouse_name }}</th>
                </tr>
            </tbody>
        </table>
        <h5>Pindah Lokasi Produk</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Jumlah Produk</th>
                    <th>Lokasi Gudang</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>
                        <input type="number" id="move_stock" name="move_stock" class="form-control" min="1"
                            value="1">
                    </th>
                    <th>
                        <select class="form-control" id="warehouse_id" name="warehouse_id">
                            <option value="">Pilih Lokasi Gudang</option>
                            @foreach ($warehouses as $w)
                                <option value="{{ $w->id_warehouse }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </th>
                </tr>
            </tbody>
        </table>
        <div class="form-group">
            <label for="note">Alasan</label>
            <input type="text" class="form-control" id="note" name="note" aria-describedby="note"
                placeholder="Masukkan alasan memindahkan produk">
        </div>
        <button type="submit" class="btn btn-success">Pindah</button>
        <a href="{{ route('productMove.index') }}" class="btn btn-danger">Batal</a>
    </form>
@endsection
