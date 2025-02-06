@extends('layouts.blank')
@section('content')
    <form method="POST" action="{{ route('sales.store') }}" enctype="multipart/form-data">
        @csrf
        <h4 class="text-center">Nota Penjualan</h4>
        <table class="table table-condensed">
            <tbody>
                <tr>
                    <th><label for="sales_invoice">Nomor Ref</label></th>
                    <th><input type="text" readonly class="form-control" id="sales_invoice" value="{{ $invoiceNumber }}">
                    </th>
                </tr>
                <tr>
                    <th><label for="id_customer">Pelanggan</label></th>
                    <th>
                        <div class="input-group">
                            <select class="form-control" id="id_customer" name="id_customer"
                                onchange="toggleOtherCustomerInput()">
                                <option value="">Pilih Pelanggan</option>
                                @foreach ($customer as $c)
                                    <option value="{{ $c->id_customer }}">{{ $c->name }}</option>
                                @endforeach
                                <option value="other">Lainnya</option>
                            </select>
                        </div>

                        <div id="other_customer_input" style="display: none;" class="pt-2">
                            <label for="customer_name">Masukkan nama pelanggan:</label>
                            <div class="pb-2">
                                <input type="text" class="form-control mb-2" id="customer_name" name="customer_name"
                                    placeholder="Nama pelanggan" />
                            </div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th><label for="date">Tanggal Order</label></th>
                    <th><input type="date" class="form-control" name="date"></th>
                </tr>
                <tr>
                    <th><label for="tax">Tarif Pajak</label></th>
                    {{-- <th><input type="text" class="form-control" name="tax" id="tax"
                            value="{{ $taxRate * 100 }}" readonly></th> --}}
                    <th>
                        <div style="display: flex; align-items: center;">
                            <input type="text" class="form-control" name="tax" id="tax"
                                value="{{ $taxRate * 100 }}" readonly style="width:50px; text-align: center;">
                            <span style="margin-left: 5px;">(%)</span>
                        </div>
                    </th>
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
                    <th>Total Stok</th>
                    <th>Opsi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select class="form-control" id="productName" onchange="updatePrice()">
                            <option value="">Pilih produk</option>
                            @foreach ($product as $p)
                                <option value="{{ $p->id_product }}" data-price="{{ $p->price }}"
                                    data-stock="{{ $p->total_stock }}">
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" id="productPrice" class="form-control" readonly>
                    </td>
                    <td>
                        <input type="number" id="productQty" class="form-control" min="1" value="1">
                    </td>
                    <td>
                        <input type="number" id="total_stock" class="form-control" disabled>
                    </td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="addProduct()">+</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <h5 class="ml-2">Daftar Produk</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Harga Satuan</th>
                    <th>Jumlah produk</th>
                    <th>Total</th>
                    <th><i class="fa-solid fa-trash-can"></i></th>
                </tr>
            </thead>
            <tbody id="productsTable"></tbody>
        </table>

        <div style="text-align: right;" class="mb-4 mt-2">
            @if ($shippingMethod->isNotEmpty())
                <a href="#modalShipping" class="btn btn-primary" data-toggle="modal">+ Biaya Pengiriman</a>
            @endif
            @if ($discount->isNotEmpty())
                <a href="#modalDiscount" class="btn btn-success" data-toggle="modal" id="discountButton">+ Diskon</a>
            @endif
        </div>

        <input type="hidden" name="total_price" id="total_price_input" value="0">
        <input type="hidden" id="hShippingCost" name="hShippingCost" value="0">
        <input type="hidden" id="hDiscount" name="hDiscount" value="0">

        <div style="text-align: right;">
            <p>Total Sebelum Pajak: <b id="totalAmount">Rp 0.00</b></p>
            <p>Pajak: <b id="taxes">Rp 0.00</b></p>
            <p>Total Setelah Pajak: <b id="total_price">Rp 0.00</b></p>
        </div>

        <div style="text-align: right;">
            <a href="{{ route('sales.index') }}" type="button" class="btn btn-danger">Batal</a>
            <a href="#modalPayment" class="btn btn-primary" data-toggle="modal">Simpan</a>
        </div>

        {{-- pilih jenis metode pembayaran --}}
        <div class="modal fade" id="modalPayment" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body" id="modalContent">
                        <div class="form-group">
                            <label for="payment_method">Metode Pembayaran</label>
                            <select class="form-control" id="payment_method" name="payment_method">
                                <option value="">Pilih metode pembayaran</option>
                                @foreach ($paymentMethod as $pay)
                                    <option value="{{ $pay->id_detail_configuration }}">{{ $pay->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                        <a href="#modalKonfirmasiPay" class="btn btn-primary" data-toggle="modal"
                            onclick="openConfirmationModal()">Verifikasi</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- cek apakah pembayaran sukses --}}
        <div class="modal fade" id="modalKonfirmasiPay" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="formKonfirmasi">
                        <div class="modal-body" id="modalContent">
                            <div class="form-group">
                                <label for="">Apakah pembayaran sukses?</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="radioPayment" id="radioYes"
                                        value="ya" required>
                                    <label class="form-check-label" for="radioYes">Ya</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="radioPayment" id="radioNo"
                                        value="tidak">
                                    <label class="form-check-label" for="radioNo">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary" onclick="getNota($sales->id_sales)">Cetak
                                Nota</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalShipping" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body" id="modalContent">
                        <div class="form-group">
                            <label for="shipping_cost">Biaya Pengiriman</label>
                            <input type="number" class="form-control" id="shipping_cost" name="shipping_cost"
                                aria-describedby="shipping_cost" placeholder="Masukkan jumlah biaya pengiriman">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="submitShippingForm()">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalDiscount" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body" id="modalContent">
                        <div class="form-group">
                            @if ($multiDiskon->isNotEmpty())
                                <p class="text-danger text-center">Jenis diskon dapat dipilih lebih dari 1x</p>
                            @else
                                <p class="text-danger text-center">Tawarkan jenis diskon yang tersedia! Setiap transaksi
                                    hanya dapat memilih 1 jenis diskon!</p>
                            @endif
                            <label for="discount">Jenis Diskon:</label>
                            @foreach ($discount as $dsc)
                                <div class="form-check">
                                    <input type="radio" class="form-check-input"
                                        id="radio{{ $dsc->id_detail_configuration }}" name="optradio"
                                        value="{{ $dsc->id_detail_configuration }}" data-name="{{ $dsc->name }}"
                                        data-value="{{ $dsc->value }}" onclick="updateDiscountValue(this)">
                                    <label class="form-check-label"
                                        for="radio{{ $dsc->id_detail_configuration }}">{{ $dsc->name }}</label>
                                </div>
                            @endforeach
                            <input type="number" class="form-control" id="discount" name="discount" value=""
                                disabled style="display: none;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="submitDiscount()">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

    </form>
@endsection

@section('javascript')
    <script>
        function getNota(id_sales) {
            console.log('ID Sales yang dikirim:', id_sales); // Debug ID
            $.ajax({
                type: 'POST',
                url: '{{ route('sales.getNota') }}',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'id_sales': id_sales
                },
                success: function(response) {
                    console.log('Response:', response);
                    window.location.href = '/sales/nota/' + id_sales;
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseJSON?.error || 'Terjadi kesalahan');
                    alert('Terjadi kesalahan: ' + (xhr.responseJSON?.error || 'URL tidak ditemukan.'));
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const btnCetakNota = document.getElementById('btnCetakNota');
            const radioYes = document.getElementById('radioYes');
            const radioButtons = document.querySelectorAll('input[name="radioPayment"]');
            // Fungsi untuk mengupdate status tombol
            function updateButtonState() {
                console.log('Radio "Ya" checked:', radioYes.checked); // Debugging
                btnCetakNota.disabled = !radioYes.checked;
            }
            // Tambahkan event listener untuk setiap radio button
            radioButtons.forEach((radio) => {
                radio.addEventListener('change', updateButtonState);
            });

            // Memanggil toggleDiscountButton saat halaman dimuat untuk memastikan tombol dalam kondisi yang tepat
            toggleDiscountButton();

            // Menambahkan event listener untuk setiap radio button
            document.querySelectorAll('input[name="optradio"]').forEach((radio) => {
                radio.addEventListener('change', function() {
                    toggleDiscountButton
                        (); // Memperbarui status tombol setelah memilih jenis diskon
                });
            });
        });

        function toggleOtherCustomerInput() {
            const customerSelect = document.getElementById("id_customer");
            const otherCustomerInput = document.getElementById("other_customer_input");
            if (customerSelect.value === "other") {
                otherCustomerInput.style.display = "block"; // Menampilkan form input nama customer
            } else {
                otherCustomerInput.style.display = "none"; // Menyembunyikan form input nama customer
            }
        }

        function openConfirmationModal() {
            // Tutup modalPayment
            $('#modalPayment').modal('hide');
            // Buka modalKonfirmasiPay setelah modalPayment ditutup
            $('#modalKonfirmasiPay').modal('show');
        }
        let totalAmount = 0;

        function getShippingCostFromTable() {
            const table = document.getElementById("productsTable");
            const rows = table.querySelectorAll("tr");
            for (let row of rows) {
                const productName = row.cells[0]?.textContent.trim();
                if (productName === "Shipping Cost") {
                    const shippingCost = parseFloat(row.cells[3]?.textContent.trim()) || 0;
                    console.log("Shipping Cost Found:", shippingCost); // Debugging
                    return shippingCost; // Kembalikan nilai Shipping Cost
                }
            }
            // Jika tidak ditemukan Shipping Cost
            console.log("Shipping Cost Not Found.");
            return 0;
        }

        function updateAmount() {
            const price = parseFloat(document.getElementById("productPrice").value) || 0;
            const qty = parseInt(document.getElementById("productQty").value) || 1;
            // Calculate amount
            const amount = price * qty;
            console.log("Debug -> Price:", price, "Qty:", qty, "Amount:", amount); // Debug log
            return amount;
        }

        function updatePrice() {
            const productSelect = document.getElementById("productName");
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute("data-price")) || 0;
            const productStock = selectedOption.getAttribute("data-stock") || 0;
            console.log("Debug -> Selected Price:", price); // Debug log
            // Set price to input field
            document.getElementById("productPrice").value = price.toFixed(2);
            document.getElementById("total_stock").value = productStock;
        }

        function updateTotals() {
            const taxInput = document.getElementById('tax');
            // Ambil value dan hilangkan simbol '%', kemudian konversikan ke desimal
            const taxRate = parseFloat(taxInput.value.replace('%', '')) / 100;
            // Sekarang taxRate akan bernilai 0.11 (untuk 11%)
            console.log('tax', taxRate); // Output: 0.11
            // const shipcost = parseFloat(document.getElementById('shipping_cost').value) || 0;
            const shipcost = getShippingCostFromTable();
            console.log('shipcost', shipcost);
            const totalWithShip = totalAmount + shipcost;
            document.getElementById('totalAmount').textContent = `Rp ${totalWithShip.toFixed(2)}`;
            const taxes = totalWithShip * taxRate;
            document.getElementById('taxes').textContent = `Rp ${taxes.toFixed(2)}`;
            const total = totalWithShip + taxes;
            document.getElementById('total_price').textContent = `Rp ${total.toFixed(2)}`;
            document.getElementById('total_price_input').value = total.toFixed(2);
        }

        const discounts = @json($discount);
        console.log(discounts);

        function addProduct() {
            const productSelect = document.getElementById("productName");
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const productName = selectedOption.text;
            const productPrice = parseFloat(document.getElementById("productPrice").value) || 0;
            const productQty = parseInt(document.getElementById("productQty").value) || 1;
            const totalStock = parseInt(document.getElementById("total_stock").value) || 0;
            let productAmount = updateAmount();
            console.log("Debug -> Adding Product:", {
                productName,
                productPrice,
                productQty,
                productAmount,
            });
            if (!productName || productPrice <= 0 || productQty <= 0) {
                alert("Please select a product and provide valid values!");
                return;
            }
            if (productQty > totalStock) {
                alert(
                    "Quantity exceeds the total stock, please set the maximum quantity according to the total stock quantity!"
                );
                return;
            }
            // Append to the product list table
            const productsTable = document.getElementById("productsTable");
            const newRow = `
                <tr>
                    <td>${productName}</td>
                    <td>${productPrice.toFixed(2)}</td>
                    <td>${productQty}</td>
                    <td>${productAmount.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa-solid fa-trash-can"></i></button>
                    </td>
                </tr>
            `;
            productsTable.insertAdjacentHTML("beforeend", newRow);
            // Update the total amount
            totalAmount += productAmount;
            updateTotals();
            // Create hidden inputs for products
            const productData = {
                id: selectedOption.value,
                name: productName,
                price: productPrice,
                quantity: productQty,
                amount: productAmount,
                // discountMember: discountMember,
            };
            // Add the product to the hidden field
            const hiddenProductsInput = document.getElementById("hidden_products_input");
            let currentProducts = JSON.parse(hiddenProductsInput.value || '[]');
            currentProducts.push(productData);
            hiddenProductsInput.value = JSON.stringify(currentProducts);
            // Clear input fields
            productSelect.value = "";
            document.getElementById("productPrice").value = "";
            document.getElementById("productQty").value = "1";
            document.getElementById("total_stock").value = "";
        }

        function submitShippingForm() {
            // Ambil nilai shipping cost dari input
            const shippingCost = parseFloat(document.getElementById('shipping_cost').value) || 0;
            document.getElementById('hShippingCost').value = shippingCost;
            // Validasi input (jika kosong atau tidak valid)
            if (shippingCost <= 0) {
                alert("Please enter a valid shipping cost.");
                return;
            }
            // Format baris baru untuk shipping cost
            const tableBody = document.getElementById('productsTable');
            const row = document.createElement('tr');
            row.innerHTML = `
            <td>Shipping Cost</td>
            <td>-</td>
            <td>1</td>
            <td>${shippingCost.toFixed(2)}</td>
            <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">
                <i class="fa-solid fa-trash-can"></i></button></td>
            `;
            // Tambahkan baris ke tabel
            tableBody.appendChild(row);
            // Tutup modal
            $('#modalShipping').modal('hide');
            // Bersihkan input shipping cost
            document.getElementById('shipping_cost').value = '';
            updateTotals();
        }

        function updateDiscountValue(radio) {
            const discountValue = radio.getAttribute('data-value');
            const discountField = document.getElementById('discount');
            const hiddenDiscountField = document.getElementById('hDiscount');
            discountField.value = discountValue;
            discountField.style.display = 'block';
            hiddenDiscountField.value = discountValue;
            updateTotals();
        }

        function getSelectedDiscountName() {
            const radios = document.getElementsByName('optradio');
            for (let radio of radios) {
                if (radio.checked) {
                    return radio.getAttribute('data-name'); // Get the name from the data-name attribute
                }
            }
            return ''; // Default if no discount is selected
        }

        function toggleDiscountButton() {
            const discountButton = document.getElementById('discountButton');
            const multiKosong = @json($multiDiskon->isEmpty()); // Menyimpan status apakah multiDiskon kosong
            const diskondipilih = document.querySelector('input[name="optradio"]:checked') !==
                null; // Mengecek apakah diskon sudah dipilih
            if (multiKosong && !diskondipilih) {
                discountButton.disabled = false
            } else if (multiKosong && diskondipilih) {
                discountButton.disabled = true
            } else if (!multiKosong && !diskondipilih) {
                discountButton.disabled = false
            } else {
                discountButton.document = false
            }
        }

        let jumDis = 0;

        function submitDiscount() {
            let disPer = 0;
            let disMem = 0;
            let disMusOr = 0;
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            console.log('diskon', discount);
            const discountHidden = document.getElementById('hDiscount'); // Ambil nilai dari hidden field
            const discountName = getSelectedDiscountName();
            console.log('diskonname', discountName);
            if (discountName === '') {
                alert('Please select a discount type.');
                return;
            }
            let finalDiscount = discount;
            console.log('final discount', finalDiscount);
            console.log('amount', totalAmount);

            if (discountName === 'Diskon Persentase') {
                finalDiscount = totalAmount * (discount / 100); // Diskon berdasarkan persentase
                disPer = finalDiscount;

                // discountHidden.value = finalDiscount;
                console.log('%', disPer);
            } else if (discountName === 'Diskon Member') {
                finalDiscount = totalAmount * (discount / 100); // Diskon member
                disMem = finalDiscount;
                // discountHidden.value = finalDiscount;
                console.log('mem', disMem);
            } else {
                finalDiscount = discount; // Diskon nominal (Musim atau Order)
                disMusOr = finalDiscount;
                // discountHidden.value = finalDiscount;
                console.log('musim/order', disMusOr);

            }
            jumDis += disPer + disMem + disMusOr;
            console.log('jumDis', jumDis);

            discountHidden.value = jumDis;
            console.log('all dis', jumDis);

            const tableBody = document.getElementById('productsTable');
            const row = document.createElement('tr');
            row.innerHTML = `
        <td>${discountName}</td>
        <td>-</td>
        <td>1</td>
        <td>${parseFloat(finalDiscount).toFixed(2)}</td>
        <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">
            <i class="fa-solid fa-trash-can"></i></button></td>
    `;
            tableBody.appendChild(row);
            $('#modalDiscount').modal('hide');
            document.getElementById('discount').value = '';
            totalAmount -= parseFloat(finalDiscount);
            updateTotals();
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
