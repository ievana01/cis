@extends('layouts.blank')
@section('content')
    <form method="POST" action="{{ route('purchase.store') }}">
        @csrf
        <h4 class="text-center">Nota Pembelian</h4>
        <table class="table table-condensed">
            <tbody>
                <tr>
                    <th><label for="purchase_invoice">Nomor Referensi</label></th>
                    <th><input type="text" readonly class="form-control" id="purchase_invoice" value="{{ $invoiceNumber }}">
                    </th>
                </tr>
                <tr>
                    <th><label for="id_supplier">Pemasok</label></th>
                    <th class="d-flex align-items-center">
                        <select class="form-control" id="id_supplier" name="id_supplier">
                            <option value="">Pilih Pemasok</option>
                            @foreach ($supplier as $s)
                                <option value="{{ $s->id_supplier }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <a href="#modalHistoryPurchase" class="btn btn-warning" data-toggle="modal">Detail</a>
                    </th>
                </tr>
                <tr>
                    <th><label for="date">Tanggal Order</label></th>
                    <th><input type="date" class="form-control" name="date" value="<?= date('Y-m-d') ?>" readonly>
                    </th>
                </tr>
                <tr>
                    @if ($receiveProdMethod->id_detail_configuration == 19)
                        <th><label for="expected_arrival">Deadline Order</label></th>
                        <th><input type="date" name="expected_arrival" id="expected_arrival" class="form-control"></th>
                    @elseif($payProd->id_detail_configuration == 21 && $receiveProdMethod->id_detail_configuration == 20)
                        <th><label for="expected_arrival">Tanggal Pengambilan barang</label></th>
                        <th><input type="date" name="expected_arrival" id="expected_arrival" class="form-control"></th>
                    @endif
                </tr>
                <tr>
                    <th><label for="warehouse">Dikirim ke</label></th>
                    <th>
                        <select class="form-control" name="id_warehouse" id="id_warehouse">
                            <option value="">Pilih gudang</option>
                            @foreach ($warehouse as $w)
                                <option value="{{ $w->id_warehouse }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </th>
                </tr>
                <tr>
                    <th><label>Tipe Pembayaran</label></th>
                    <th><input type="text" class="form-control" value="{{ $payProd->name }}" disabled></th>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="products" id="hidden_products_input" value="[]">
        <h5 class="ml-2">Produk</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Kuantitas</th>
                    <th>Opsi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select class="form-control" id="productName" onchange="updateCost()">
                            <option value="">Pilih produk</option>
                            @foreach ($product as $p)
                                <option value="{{ $p->id_product }}" data-cost="{{ $p->cost ?? 0 }}">
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" id="productCost" class="form-control">
                    </td>
                    <td>
                        <input type="number" id="productQty" class="form-control" min="1" value="1">
                    </td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="addProduct()">+</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <h5 class="ml-2">Daftar Produk yang Dipesan</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Harga Satuan</th>
                    <th>Kuantitas</th>
                    <th>Total</th>
                    <th><i class="fa-solid fa-trash-can"></i></th>
                </tr>
            </thead>
            <tbody id="productsTable"></tbody>
        </table>
        <input type="hidden" name="total_price" id="total_price_input" value="0">
        <div style="text-align: right;">
            <p>Jumlah sebelum pajak: <b id="totalAmount">Rp 0.00</b></p>
            <p>Pajak: <b id="taxes">Rp 0.00</b></p>
            <p>Total: <b id="total_price">Rp 0.00</b></p>
        </div>
        <div style="text-align: right;">
            <a href="{{ route('purchase.index') }}" type="button" class="btn btn-danger">Batal</a>

            <button type="submit" class="btn btn-primary" onclick="getNota($purchase->id_purchase)">Buat Pesanan</button>
        </div>
    </form>
    <div class="modal fade" id="modalHistoryPurchase" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-body" id="modalContent">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal Pembelian</th>
                                <th>Nama Pemasok</th>
                                <th>Nama Produk</th>
                                <th>Jumlah Produk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hisPurchase as $h)
                                <tr>
                                    <th>{{ $h->date }}</th>
                                    <th>{{ $h->supplier_name }}</th>
                                    <th>{{ $h->product_name }}</th>
                                    <th>{{ $h->move_stock }}</th>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        function getNota(id_purchase) {
            console.log('ID Purchase yang dikirim:', id_purchase); // Debug ID
            $.ajax({
                type: 'POST',
                url: '{{ route('purchase.getNota') }}',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'id_purchase': id_purchase
                },
                success: function(response) {
                    console.log('Response:', response);
                    window.location.href = '/purchase/nota/' + id_purchase;
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseJSON?.error || 'Terjadi kesalahan');
                    alert('Terjadi kesalahan: ' + (xhr.responseJSON?.error || 'URL tidak ditemukan.'));
                }
            });
        }

        let totalAmount = 0;

        function updateCost() {
            var productSelect = document.getElementById("productName");
            var selectedOption = productSelect.options[productSelect.selectedIndex];
            var price = parseFloat(selectedOption.getAttribute("data-cost")) || 0;
            console.log("Debug -> Selected Price:", price);
            document.getElementById("productCost").value = price.toFixed(2);
        }

        function updateAmount() {
            var price = parseFloat($('#productCost').val()) || 0;
            console.log('upd', price);

            var qty = parseInt($('#productQty').val()) || 1;
            console.log('upd', qty);

            var amount = price * qty;
            console.log("Debug -> Price:", price, "Qty:", qty, "Amount:", amount); // Debug log
            return amount;
        }

        function updateTotals() {
            document.getElementById("totalAmount").innerText = `Rp ${totalAmount.toFixed(2)}`;

            // Hitung pajak (misalnya 10%)
            const taxRate = 0;
            const taxes = totalAmount * taxRate;
            document.getElementById("taxes").innerText = `Rp ${taxes.toFixed(2)}`;

            // Hitung total keseluruhan (amount + pajak)
            const totalPrice = totalAmount + taxes;
            document.getElementById("total_price").innerText = `Rp ${totalPrice.toFixed(2)}`;
            document.getElementById('total_price_input').value = totalPrice.toFixed(2);
        }

        function addProduct() {
            var productSelect = document.getElementById("productName");
            var selectedOption = productSelect.options[productSelect.selectedIndex];
            var productName = selectedOption.text;
            var productCost = parseFloat($('#productCost').val()) || 0;
            var qty = parseInt($('#productQty').val()) || 1;
            console.log('prodcos', productCost);

            var productAmount = updateAmount();
            console.log("Debug -> Adding Product:", {
                productName,
                productCost,
                qty,
                productAmount,
            });
            if (!productName || productCost <= 0 || productQty <= 0) {
                alert("Please select a product and provide valid values!");
                return;
            }
            const productsTable = document.getElementById("productsTable");
            const newRow = `
                <tr>
                    <td>${productName}</td>
                    <td>${productCost.toFixed(2)}</td>
                    <td>${qty}</td>
                    <td>${productAmount.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa-solid fa-trash-can"></i></button>
                    </td>
                </tr>
            `;
            productsTable.insertAdjacentHTML("beforeend", newRow);

            totalAmount += productAmount;
            updateTotals();

            const productData = {
                id: selectedOption.value,
                name: productName,
                cost: productCost,
                quantity: qty,
                amount: productAmount
            };

            const hiddenProductsInput = document.getElementById("hidden_products_input");
            let currentProducts = JSON.parse(hiddenProductsInput.value || '[]');
            currentProducts.push(productData);
            hiddenProductsInput.value = JSON.stringify(currentProducts);

            // Clear input fields
            productSelect.value = "";
            document.getElementById("productCost").value = "";
            document.getElementById("productQty").value = "1";
        }

        function removeRow(button) {
            const row = button.closest("tr");
            const amount = parseFloat(row.children[3].textContent) || 0;

            // Kurangi total amount
            totalAmount -= amount;
            updateTotals();

            // Hapus baris dari tabel
            row.remove();
        }
    </script>
@endsection
