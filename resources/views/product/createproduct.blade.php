@extends('layouts.blank')
@section('content')
    <form method="POST" action="{{ route('product.store') }}" enctype="multipart/form-data">
        @csrf
        <h4 class="text-center">Tambah Produk Baru</h4>
        <div class="form-group">
            <label for="id_image ">Foto Produk</label>
            <input type="file" class="form-control-file" id="id_image" name="file_images[]" multiple>
            <small id="id_image" class="form-text text-muted">Pilih foto produk</small>
        </div>
        <div id="previewContainer" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <!-- Image previews will appear here -->
        </div>
        <div class="form-group">
            <label for="name">Nama Produk</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Masukkan nama produk">
        </div>
        <div class="form-group">
            <label for="description">Deskripsi</label>
            <input type="text" class="form-control" id="description" name="description" aria-describedby="description"
                placeholder="Masukkan deskripsi produk">
        </div>
        <div class="form-group">
            <label class="control-label">Kategori Produk</label>
            <select class="form-control" name="category_id" id="category_id ">
                <option value="">Pilih kategori</option>
                @foreach ($category as $c)
                    <option value="{{ $c->id_category }}">{{ $c->id_category }} - {{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="total_stock">Jumlah produk</label>
            <input type="text" class="form-control" id="total_stock" name="total_stock" aria-describedby="total_stock"
                placeholder="Masukkan jumlah produk yang dimiliki">
        </div>
        <div class="form-group">
            <label for="cost">Harga Pokok Produk</label>
            <input type="text" class="form-control" id="cost" name="cost" aria-describedby="cost"
                placeholder="Masukkan harga pokok produk">
        </div>
        <div class="form-group">
            <label for="profit">Keuntungan Produk (%)</label>
            <input type="number" class="form-control" id="profit" name="profit" aria-describedby="profit"
                placeholder="Berapa banyak keuntungan yang anda inginkan untuk produk ini?">
        </div>
        <div class="form-group">
            <label for="unit">Tipe Produk</label>
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
            <label for="min_total_stock">Minimum Stok di Gudang</label>
            <input type="text" class="form-control" id="min_total_stock" name="min_total_stock"
                aria-describedby="min_total_stock" placeholder="Masukkan minimum stok di gudang untuk produk ini">
        </div>
        <div class="form-group">
            <label class="control-label">Lokasi Penyimpan Produk</label>
            <select class="form-control" name="warehouse_id" id="warehouse_id">
                @foreach ($warehouse as $w)
                    <option value="">Pilih lokasi</option>
                    <option value="{{ $w->id_warehouse }}">{{ $w->id_warehouse }} - {{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="text-align: right;">
            <a href="{{ route('product.index') }}" class="btn btn-danger" type="button">Cancel</a>
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>

        </div>
    </form>
@endsection

@section('javascript')
    <script>
        var cost = document.getElementById('cost');
        console.log(cost);
        var profit = document.getElementById('profit');
        console.log(profit);

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
