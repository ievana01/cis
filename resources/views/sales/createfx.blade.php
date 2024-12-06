@extends('layouts.btemplate')
@section('content')
    <form method="POST" action="{{ route('sales.store') }}">
        @csrf
        <div class="form-inline mb-3">
            <label for="sales_invoice">Sales Invoice</label>
            <div class="col-sm-10">
                <input type="text" readonly class="form-control " id="sales_invoice" value="{{ $invoiceNumber }}">
            </div>
        </div>
        <div class="mb-3">
            <label for="id_customer">Pembeli</label>
            <select class="form-control" id="id_customer" name="id_customer">
                <option value="">Pilih Pembeli</option>
                @foreach ($customer as $c)
                    <option value="{{ $c->id_customer }}"> {{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="created_at">Tanggal Order</label>
            <input type="date" class="form-control" name="created_at" aria-describedby="created_at">
        </div>
        <div>
            <h6 style="font-weight: bold">Rincian Produk</h6>
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Sub Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select class="form-control product-select" name="barang[]">
                                <option value="">Pilih produk</option>
                                @foreach ($product as $p)
                                    <option value="{{ $p->id_product }}" data-price="{{ $p->price }}">
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="total_quantity[]" class="form-control quantity-input" min="1"
                                value="1" required>
                        </td>
                        <td>
                            <input type="number" name="price[]" class="form-control product-price" required readonly>
                        </td>
                        <td>
                            <input type="number" name="total_price[]" class="form-control subtotal-input" required
                                readonly>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove-row">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="ml-2">
            <button type="button" class="btn btn-primary mb-3">Tambah Produk</button>
        </div>
        <div class="form-inline mb-3">
            <label for="total_price">Total Harga</label>
            <div class="col-sm-10">
                <input type="text" readonly class="form-control " id="total_price">
            </div>
        </div>
    </form>
@endsection
@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateSubtotal(row) {
                const price = parseFloat(row.querySelector('.product-price').value) || 0;
                const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
                const subtotalInput = row.querySelector('.subtotal-input');
                const subtotal = price * quantity;
                subtotalInput.value = subtotal.toFixed(2);
            }

            function updateTotalPrice() {
                let total = 0;
                document.querySelectorAll('.subtotal-input').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                document.getElementById('total_price').value = total.toFixed(2);
            }

            function addNewRow() {
                const tbody = document.querySelector('table tbody');
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>
                        <select class="form-control product-select" name="barang[]">
                            <option value="">Pilih produk</option>
                            @foreach ($product as $p)
                                <option value="{{ $p->id_product }}" data-price="{{ $p->price }}">
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="total_quantity[]" class="form-control quantity-input" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="number" name="price[]" class="form-control product-price" readonly>
                    </td>
                    <td>
                        <input type="number" name="total_price[]" class="form-control subtotal-input" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger remove-row">Hapus</button>
                    </td>
                `;
                tbody.appendChild(newRow);

                // Tambahkan event listener untuk elemen baru
                const productSelect = newRow.querySelector('.product-select');
                const quantityInput = newRow.querySelector('.quantity-input');
                const removeButton = newRow.querySelector('.remove-row');

                productSelect.addEventListener('change', function() {
                    const priceInput = newRow.querySelector('.product-price');
                    const selectedOption = this.options[this.selectedIndex];
                    const price = selectedOption.getAttribute('data-price') || 0;
                    priceInput.value = parseFloat(price).toFixed(2);

                    // Update subtotal dan total harga
                    updateSubtotal(newRow);
                    updateTotalPrice();
                });

                quantityInput.addEventListener('input', function() {
                    updateSubtotal(newRow);
                    updateTotalPrice();
                });

                removeButton.addEventListener('click', function() {
                    newRow.remove();
                    updateTotalPrice();
                });
            }

            // Event listener untuk elemen awal
            document.querySelector('.btn-primary.mb-3').addEventListener('click', addNewRow);

            document.querySelectorAll('.product-select').forEach(select => {
                select.addEventListener('change', function() {
                    const row = this.closest('tr');
                    const priceInput = row.querySelector('.product-price');
                    const selectedOption = this.options[this.selectedIndex];
                    const price = selectedOption.getAttribute('data-price') || 0;
                    priceInput.value = parseFloat(price).toFixed(2);

                    updateSubtotal(row);
                    updateTotalPrice();
                });
            });

            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('tr');
                    updateSubtotal(row);
                    updateTotalPrice();
                });
            });

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-row')) {
                    const row = e.target.closest('tr');
                    row.remove();
                    updateTotalPrice();
                }
            });
        });
    </script>
@endsection
