@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('product.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="id_image ">Masukkan gambar produk</label>
            <input type="file" class="form-control-file" id="id_image" name="file_images[]" multiple>
        </div>
        <div id="previewContainer" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <!-- Image previews will appear here -->
        </div>
        <div class="form-group">
            <label for="name">Nama</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama produk">
        </div>
        <div class="form-group">
            <label for="description">Deskripsi</label>
            <input type="text" class="form-control" id="description" name="description" aria-describedby="description"
                placeholder="Masukkan deskripsi produk">
        </div>
        <div class="form-group">
            <label class="control-label">Nama Pemasok</label>
            <select class="form-control" name="supplier_id" id="supplier_id">
                <option value="">Pilih Pemasok</option>
                @foreach ($supplier as $s)
                    <option value="{{ $s->id_supplier }}">{{ $s->id_supplier }} - {{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="control-label">Kategori Produk</label>
            <select class="form-control" name="category_id" id="category_id ">
                <option value="">Pilih Kategori</option>
                @foreach ($category as $c)
                    <option value="{{ $c->id_category }}">{{ $c->id_category }} - {{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="total_stock">Jumlah Produk</label>
            <input type="text" class="form-control" id="total_stock" name="total_stock" aria-describedby="total_stock"
                placeholder="Masukkan jumlah produk yang dimiliki">
        </div>
        <div class="form-group">
            <label for="price">Harga Jual</label>
            <input type="text" class="form-control" id="price" name="price" aria-describedby="price"
                placeholder="Masukkan harga jual barang">
        </div>
        <div class="form-group">
            <label for="cost">Harga Pokok</label>
            <input type="text" class="form-control" id="cost" name="cost" aria-describedby="cost"
                placeholder="Masukkan harga pokok produk">
        </div>
        <div class="form-group">
            <label for="tax">Pajak Penjualan</label>
            <select class="form-control" id="tax" name="tax">
                @foreach ($tax as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="unit">Jenis Produk</label>
            <div>
                @foreach ($unit as $option)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="unit" id="unit_{{ $option }}"
                            value="{{ $option }}">
                        <label class="form-check-label" for="unit_{{ $option }}">{{ $option }}</label>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label for="min_total_stock">Minimum Stok</label>
            <input type="text" class="form-control" id="min_total_stock" name="min_total_stock"
                aria-describedby="min_total_stock" placeholder="Silahkan atur minimum stok pada produk ini">
        </div>
        <div class="form-group">
            <label class="control-label">Lokasi Produk</label>
            <select class="form-control" name="warehouse_id" id="warehouse_id">
                @foreach ($warehouse as $w)
                    <option value="{{ $w->id_warehouse }}">{{ $w->id_warehouse }} - {{ $w->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>

        </div>
    </form>
@endsection

@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productImages = document.getElementById('id_image');
            console.log('productImages:', productImages); // Should log the input element

            const previewContainer = document.getElementById('previewContainer');
            console.log('previewContainer:', previewContainer); // Should log the preview container

            if (productImages) {
                productImages.addEventListener('change', function(event) {
                    previewContainer.innerHTML = '';
                    Array.from(event.target.files).forEach(file => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.style.width = '100px';
                                img.style.height = '100px';
                                img.style.objectFit = 'cover';
                                img.style.border = '1px solid #ddd';
                                img.style.borderRadius = '5px';
                                img.style.margin = '5px';
                                previewContainer.appendChild(img);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                });
            }
        });
    </script>
@endsection
