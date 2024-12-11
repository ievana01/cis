@extends('layouts.blank')
@section('content')
    <form method="POST" action="{{ route('product.store') }}" enctype="multipart/form-data">
        @csrf
        <h4 class="text-center">Add New Product</h4>
        <div class="form-group">
            <label for="id_image ">Insert photo product</label>
            <input type="file" class="form-control-file" id="id_image" name="file_images[]" multiple>
        </div>
        <div id="previewContainer" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <!-- Image previews will appear here -->
        </div>
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name"
                placeholder="Insert product name">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <input type="text" class="form-control" id="description" name="description" aria-describedby="description"
                placeholder="Insert description product">
        </div>
        <div class="form-group">
            <label class="control-label">Supplier Name</label>
            <select class="form-control" name="supplier_id" id="supplier_id">
                <option value="">Choose supplier</option>
                @foreach ($supplier as $s)
                    <option value="{{ $s->id_supplier }}">{{ $s->id_supplier }} - {{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="control-label">Category Product</label>
            <select class="form-control" name="category_id" id="category_id ">
                <option value="">Choose Category</option>
                @foreach ($category as $c)
                    <option value="{{ $c->id_category }}">{{ $c->id_category }} - {{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="total_stock">Initial Product Quantity</label>
            <input type="text" class="form-control" id="total_stock" name="total_stock" aria-describedby="total_stock"
                placeholder="Insert initial stock product">
        </div>
        <div class="form-group">
            <label for="price">Product Price</label>
            <input type="text" class="form-control" id="price" name="price" aria-describedby="price"
                placeholder="Insert price product">
        </div>
        <div class="form-group">
            <label for="cost">Product Cost</label>
            <input type="text" class="form-control" id="cost" name="cost" aria-describedby="cost"
                placeholder="Insert cost product">
        </div>
        <div class="form-group">
            <label for="unit">Type of Product</label>
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
            <label for="min_total_stock">Minimum Stock</label>
            <input type="text" class="form-control" id="min_total_stock" name="min_total_stock"
                aria-describedby="min_total_stock" placeholder="Please set the minimum stock on this product">
        </div>
        <div class="form-group">
            <label class="control-label">Location Product</label>
            <select class="form-control" name="warehouse_id" id="warehouse_id">
                @foreach ($warehouse as $w)
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
