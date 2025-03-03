@extends('layouts.blank')
@section('content')
    <form action="{{ route('delivery-note.storePindah') }}" method="POST">
        <h4 class="text-center">Pindah Produk</h4>
        @csrf
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th><label for="invoice_number">No Ref</label></th>
                    <th><input type="text" readonly class="form-control" id="invoice_number" value="{{ $invoiceNumber }}">
                    </th>
                </tr>
                <tr>
                    <th><label for="date">Tanggal Pindah</label></th>
                    <th><input type="text" class="form-control" name="date_display" id="dateInput"
                            placeholder="Silahkan pilih tanggal" onfocus="this.type='date'" onblur="formatDate(this)"></th>
                    <input type="hidden" name="date" id="dateHidden">

                </tr>
            </tbody>
        </table>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <td>Gudang Pengirim</td>
                    <td>Gudang Penerima</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>
                        <select class="form-control" name="warehouses_id_in" id="warehouses_id_in">
                            <option value="">Pilih gudang</option>
                            @foreach ($warehouse as $w)
                                <option value="{{ $w->id_warehouse }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th>
                        <select class="form-control" name="warehouses_id_out" id="warehouses_id_out">
                            <option value="">Pilih gudang</option>
                            @foreach ($warehouse as $w)
                                <option value="{{ $w->id_warehouse }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </th>
                </tr>
            </tbody>
        </table>

        <div class="mb-3">
            <label for="note">Alasan</label>
            <textarea class="form-control" name="note" placeholder="Masukkan alasan pemindahan produk" id="note"></textarea>
        </div>

        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Stok</th>
                    <th>Jumlah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>
                        <select class="form-control" id="product_list">
                            <option value="">Pilih Produk</option>
                        </select>
                    </th>
                    <th>
                        <input type="text" id="stock" class="form-control" readonly>
                    </th>
                    <th>
                        <input type="number" id="quantity" class="form-control" min="1" value="1">
                    </th>
                    <th>
                        <button type="button" class="btn btn-primary" onclick="addProduct()">+</button>
                    </th>
                </tr>
            </tbody>
        </table>

        <h5 class="ml-2">Daftar produk yang dipindah</h5>
        <input type="hidden" name="products" id="productsData">
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="productsTable"></tbody>
        </table>

        <div style="text-align: end">
            <button type="submit" class="btn btn-primary">Pindah</button>
            <a href="{{ route('pindahProduk.index') }}" class="btn btn-danger">Batal</a>
        </div>
    </form>
@endsection

@section('javascript')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let warehouseSelect = document.getElementById('warehouses_id_in');
            let productSelect = document.getElementById('product_list');
            let stockInput = document.getElementById('stock');

            warehouseSelect.addEventListener('change', function() {
                let warehouseId = this.value;

                if (warehouseId) {
                    fetch(`/get-products/${warehouseId}`)
                        .then(response => response.json())
                        .then(data => {
                            productSelect.innerHTML = '<option value="">Pilih Produk</option>';

                            data.forEach(product => {
                                let option = document.createElement('option');
                                option.value = product.id_product;
                                option.dataset.stock = product.stock;
                                option.textContent = `${product.name}`;
                                productSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error fetching products:', error));
                }
            });

            productSelect.addEventListener('change', function() {
                let selectedOption = productSelect.options[productSelect.selectedIndex];
                stockInput.value = selectedOption.dataset.stock || 0;
            });
        });

        function formatDate(input) {
            if (input.value) {
                let date = new Date(input.value);
                let formattedDate = date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });

                input.type = 'text';
                input.value = formattedDate;
                document.getElementById('dateHidden').value = date.toISOString().split('T')[0];

            } else {
                input.type = 'text';
                document.getElementById('dateHidden').value = '';
            }
        };
        let products = [];

        function addProduct() {
            let productSelect = document.getElementById('product_list');
            let stockInput = document.getElementById('stock');
            let quantityInput = document.getElementById('quantity');
            let productsTable = document.getElementById('productsTable');
            let productsDataInput = document.getElementById('productsData');

            let productId = productSelect.value;
            let productName = productSelect.options[productSelect.selectedIndex].text;
            let stock = parseInt(stockInput.value) || 0;
            let quantity = parseInt(quantityInput.value) || 0;

            if (!productId || quantity <= 0 || quantity > stock) {
                alert('Harap pilih produk dan pastikan jumlah tidak melebihi stok.');
                return;
            }

            // Cek apakah produk sudah ada di daftar
            let existingProduct = products.find(p => p.id === productId);
            if (existingProduct) {
                existingProduct.quantity += quantity;
            } else {
                products.push({
                    id: productId,
                    name: productName,
                    quantity: quantity
                });
            }

            // Hapus produk yang dipilih dari dropdown
            productSelect.remove(productSelect.selectedIndex);

            updateProductsTable();
            resetForm();
        }

        function updateProductsTable() {
            let productsTable = document.getElementById('productsTable');
            let productsDataInput = document.getElementById('productsData');

            productsTable.innerHTML = ''; // Kosongkan tabel sebelum update

            products.forEach((product, index) => {
                let row = `
            <tr>
                <td>${product.name}</td>
                <td>${product.quantity}</td>
                <td><button type="button" class="btn btn-danger" onclick="removeProduct(${index})">Hapus</button></td>
            </tr>
        `;
                productsTable.innerHTML += row;
            });

            // Simpan data produk dalam input hidden untuk dikirim ke backend
            productsDataInput.value = JSON.stringify(products);
        }

        function removeProduct(index) {
            let product = products[index];

            // Tambahkan kembali produk ke dropdown
            let productSelect = document.getElementById('product_list');
            let option = document.createElement('option');
            option.value = product.id;
            option.textContent = product.name;
            productSelect.appendChild(option);

            // Hapus produk dari daftar
            products.splice(index, 1);
            updateProductsTable();
        }

        function resetForm() {
            document.getElementById('product_list').selectedIndex = 0;
            document.getElementById('stock').value = '';
            document.getElementById('quantity').value = 1;
        }
    </script>
@endsection
