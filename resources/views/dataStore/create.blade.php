@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('dataStore.store') }}" enctype="multipart/form-data">
        @csrf
        <h4 class="font-weight-bold">Data Toko Baru</h4>
        <div class="form-group">
            <label for="logo">Logo</label><br>
            <input type="file" class="form-control-file" id="logo" name="logo">
            <small id="logo" class="form-text text-muted">Masukkan logo untuk toko anda</small>
        </div>
        <div id="previewContainer" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <!-- Image previews will appear here -->
        </div>
        <div class="form-group">
            <label for="name">Nama</label>
            <input class="form-control" type="text" name="name" id="name">
            <small id="name" class="form-text text-muted">Masukkan nama toko anda</small>
        </div>
        <div class="form-group">
            <label for="address">Alamat</label>
            <input class="form-control" type="text" name="address" id="address">
            <small id="address  " class="form-text text-muted">Masukkan alamat toko anda</small>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input class="form-control" type="text" name="email" id="email">
            <small id="email" class="form-text text-muted">Masukkan email toko anda</small>
        </div>
        <div class="form-group">
            <label for="contact_person">Narahubung</label>
            <input class="form-control" type="text" name="contact_person" id="contact_person">
            <small id="email" class="form-text text-muted">Masukkan nama narahubung toko anda</small>
        </div>
        <div class="form-group">
            <label for="phone_number">Nomor Telepon</label>
            <input class="form-control" type="text" name="phone_number" id="phone_number">
            <small id="phone_number" class="form-text text-muted">Masukkan nomor telepon untuk menghubungi toko anda</small>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
@endsection

@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productImages = document.getElementById('logo');
            console.log('logo:', productImages); // Should log the input element

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
