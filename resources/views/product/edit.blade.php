@extends('layouts.btemplate')

@section('content')
    <form method="POST" action="{{ route('product.update', $product->id_product) }}">
        @csrf
        @method('PUT')

        <div class="form-inline mb-3">
            <label for="id_product">ID</label>
            <div class="col-sm-10">
                <input type="text" readonly class="form-control " id="id_product" value="{{ $product->id_product }}">
            </div>
        </div>
        <div class="form-group">
            <label for="name">Nama</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                value="{{ $product->name }}" placeholder="Masukkan nama produk">
        </div>

        <div class="form-group">
            <label for="description">Deskripsi</label>
            <input type="text" class="form-control" id="description" name="description" aria-describedby="description"
                value="{{ $product->description }}" placeholder="Masukkan deskripsi produk">
        </div>

        <div class="form-group">
            <label class="control-label">Nama Pemasok</label>
            <select class="form-control" name="supplier_id" id="supplier_id">
                @foreach ($supplier as $s)
                    <option value="{{ $s->id_supplier }}" {{ $s->id_supplier == $product->supplier_id ? 'selected' : '' }}>
                        {{ $s->id_supplier }} - {{ $s->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="control-label">Kategori Produk</label>
            <select class="form-control" name="category_id" id="category_id">
                <option value="">Pilih Kategori</option>
                @foreach ($category as $c)
                    <option value="{{ $c->id_category }}" {{ $c->id_category == $product->category_id ? 'selected' : '' }}>
                        {{ $c->id_category }} - {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="total_stock">Jumlah Produk</label>
            <input type="text" class="form-control" id="total_stock" name="total_stock" aria-describedby="total_stock"
                value="{{ $product->total_stock }}" placeholder="Masukkan jumlah produk yang dimiliki">
        </div>

        <div class="form-group">
            <label for="price">Harga Jual</label>
            <input type="text" class="form-control" id="price" name="price" aria-describedby="price"
                value="{{ $product->price }}" placeholder="Masukkan harga jual barang">
        </div>

        <div class="form-group">
            <label for="cost">Harga Pokok</label>
            <input type="text" class="form-control" id="cost" name="cost" aria-describedby="cost"
                value="{{ $product->cost }}" placeholder="Masukkan harga pokok produk">
        </div>

        <div class="form-group">
            <label for="unit">Jenis Produk</label>
            <div>
                @foreach ($unitOptions as $option)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="unit" id="unit_{{ $option }}"
                            value="{{ $option }}" {{ $option == $product->unit ? 'checked' : '' }}>
                        <label class="form-check-label" for="unit_{{ $option }}">{{ $option }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label for="min_total_stock">Minimum Stok</label>
            <input type="text" class="form-control" id="min_total_stock" name="min_total_stock"
                value="{{ $product->min_total_stock }}" aria-describedby="min_total_stock"
                placeholder="Silahkan atur minimum stok pada produk ini">
        </div>

        <div class="form-group">
            <label class="control-label">Lokasi Produk</label>
            <select class="form-control" name="warehouse_id" id="warehouse_id" disabled>
                @foreach ($warehouse as $w)
                    <option value="{{ $w->id_warehouse }}" >
                        {{ $w->id_warehouse }} - {{ $w->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection
