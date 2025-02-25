@extends('layouts.btemplate')

@section('content')
    <form method="POST" action="{{ route('product.update', $product->id_product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <h4 class="font-weight-bold">Edit Produk</h4>
        <div class="form-inline mb-3">
            <label for="id_product">ID</label>
            <div class="col-sm-10">
                <input type="text" readonly class="form-control " id="id_product" value="{{ $product->id_product }}">
            </div>
        </div>

        <div class="form-group">
            <label for="imageUpload">Foto Produk</label><br>
            <!-- Menampilkan foto produk sebelumnya -->
            <div id="existingImages" style="display: flex; gap: 10px; flex-wrap: wrap;" class="mb-2">
                @foreach ($images as $image)
                    <div>
                        <img src="{{ asset('storage/' . $image) }}" alt="Foto Produk" width="200" height="200" />
                    </div>
                @endforeach
            </div>

            <!-- Input file untuk mengganti gambar -->
            <input type="file" class="form-control-file" id="imageUpload" name="imageUpload">
            @error('file_images')
                <small class="text-danger">{{ $message }}</small>
            @enderror
            <small id="file_image" class="form-text text-muted">Masukkan foto produk</small>

            <!-- Preview gambar baru yang dipilih -->
            <div id="previewContainer" style="display: flex; gap: 10px; flex-wrap: wrap;"></div>
        </div>
        {{-- <div class="form-group">
            <label for="file_image">Foto Produk</label><br>
            <img src="{{ asset('storage/product_images/' . $images) }}" alt="Foto Produk" width="50"
                height="50" />
            <input type="file" class="form-control-file" id="logo" name="logo">
            <small id="file_image" class="form-text text-muted">Masukkan foto produk</small>
        </div>
        <div id="previewContainer" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <!-- Image previews will appear here -->
        </div> --}}

        <div class="form-group">
            <label for="name">Nama</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                value="{{ $product->name }}" placeholder="Masukkan nama produk">
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Deskripsi</label>
            <input type="text" class="form-control" id="description" name="description" aria-describedby="description"
                value="{{ $product->description }}" placeholder="Masukkan deskripsi produk">
        </div>

        <div class="form-group">
            <label class="control-label">Kategori Produk</label>
            <select class="form-control" name="category_id" id="category_id">
                <option value="">Pilih Kategori</option>
                @foreach ($category as $c)
                    <option value="{{ $c->id_category }}" {{ $c->id_category == $product->category_id ? 'selected' : '' }}>
                        {{ $c->code_category }} - {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if ($konfigSubCat != null)
            <div class="form-group">
                <label class="control-label">Sub Kategori Produk</label>
                <select class="form-control" name="sub_category_id" id="sub_category_id">
                    <option value="">Pilih Sub Kategori</option>
                    @foreach ($subCat as $sc)
                        <option value="{{ $sc->id_sub_category }}"
                            {{ $sc->id_sub_category == $product->sub_categories_id ? 'selected' : '' }}>
                            {{ $sc->code_sub_category }} - {{ $sc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="form-group">
            <label for="total_stock">Jumlah Produk</label>
            <input type="text" class="form-control" id="total_stock" name="total_stock" aria-describedby="total_stock"
                value="{{ $product->total_stock }}" readonly>
            @error('total_stock')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        @if ($pemProd->id_detail_configuration == 17)
            <div class="form-group">
                <label for="cost">Harga Pokok</label>
                <input type="text" class="form-control" id="cost" name="cost" aria-describedby="cost"
                    value="{{ $product->cost }}" placeholder="Masukkan harga pokok produk" disabled>
            </div>
        @else
            <div class="form-group">
                <label for="cost">Harga Pokok</label>
                <input type="text" class="form-control" id="cost" name="cost" aria-describedby="cost"
                    value="{{ $product->cost }}" placeholder="Masukkan harga pokok produk">
            </div>
            @error('cost')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        @endif

        <div class="form-group">
            <label for="profit">Keuntungan(%)</label>
            <input type="number" class="form-control" id="profit" name="profit" aria-describedby="profit"
                value="{{ $product->profit }}" placeholder="Masukkan keuntungan untuk produk ini">
            @error('profit')
                <small class="text-danger">{{ $message }}</small>
            @enderror
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
            <label for="min_total_stock">Minimum Stok di Gudang</label>
            <input type="text" class="form-control" id="min_total_stock" name="min_total_stock"
                value="{{ $product->min_total_stock }}" aria-describedby="min_total_stock"
                placeholder="Silahkan atur minimum stok pada produk ini">
            @error('min_total_stock')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label class="control-label">Lokasi Produk</label>
            <input type="text" class="form-control" id="warehouse_id" name="warehouse_id"
                value="{{ $warehouse->name }}" disabled>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('product.index') }}" class="btn btn-danger">Batal</a>
    </form>
@endsection
@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productImages = document.getElementById('imageUpload');
            console.log('imageUpload:', productImages); // Should log the input element

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
