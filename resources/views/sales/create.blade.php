@extends('layouts.blank')
@section('content')
    <form method="POST" action="{{ route('sales.store') }}" enctype="multipart/form-data">
        @csrf
        <h4 class="text-center">Order Penjualan Baru</h4>
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
                    <th>
                        <input type="text" class="form-control" name="date" id="dateInput"
                            placeholder="Silahkan pilih tanggal" onfocus="this.type='date'" onblur="formatDate(this)">
                    </th>
                    <input type="hidden" name="date" id="dateHidden">
                </tr>
                <tr>
                    <th><label for="tax">Tarif Pajak</label></th>
                    <th>
                        <div style="display: flex; align-items: center;">
                            <input type="text" class="form-control" name="tax" id="tax"
                                value="{{ $taxRate * 100 }}" readonly style="width:50px; text-align: center;">
                            <span style="margin-left: 5px;">(%)</span>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th><label for="">Metode Pengiriman</label></th>
                    <th>
                        @php
                            $dikirim = $pengiriman->firstWhere('name', 'Pesanan dikirim oleh toko');
                            $diambil = $pengiriman->firstWhere('name', 'Pesanan diambil di toko');
                        @endphp

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metode_pengiriman" id="dikirim"
                                value="dikirim" {{ $dikirim ? '' : 'disabled' }}>
                            <label class="form-check-label" for="dikirim">
                                Pesanan dikirim oleh toko
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metode_pengiriman" id="diambil"
                                value="diambil" {{ $diambil ? '' : 'disabled' }}>
                            <label class="form-check-label" for="diambil">
                                Pesanan diambil di toko
                            </label>
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
                                    data-stock="{{ $p->total_stock }}" data-categoryId ="{{ $p->category_id }}">
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
            @foreach ($shippingMethod as $spm)
                @if ($spm->id_detail_configuration == 6)
                    <a href="#modalShipping" class="btn btn-primary" data-toggle="modal">+ Biaya Pengiriman</a>
                @endif
            @endforeach
            @if ($discountUmum->isNotEmpty())
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
            <a href="#modalPayment" class="btn btn-primary" data-toggle="modal">Simpan</a>
            <a href="{{ route('sales.index') }}" type="button" class="btn btn-danger">Batal</a>
        </div>

        {{-- pilih jenis metode pembayaran --}}
        <div class="modal fade" id="modalPayment" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body" id="modalContent">
                        <div id="text-dikirim" style="display: none">
                            <div>
                                <label for="expected_arrival">Perkiraan tanggal kirim</label>
                                <input type="text" class="form-control" name="delivery_date" id="dateInput"
                                    placeholder="Silahkan pilih tanggal" onfocus="this.type='date'"
                                    onblur="formatDate(this)">
                                <input type="hidden" name="delivery_date" id="delivery_date">
                            </div>
                            <div>
                                <label for="recipient_name">Nama Penerima</label>
                                <input type="text" class="form-control" name="recipient_name" id="recipient_name"
                                    placeholder="Masukkan nama penerima pesanan">
                            </div>
                            <div>
                                <label for="recipient_address">Alamat Penerima</label>
                                <input type="text" class="form-control" name="recipient_address"
                                    id="recipient_address" placeholder="Masukkan alamat penerima pesanan">
                            </div>
                            <div>
                                <label for="recipient_phone_num">Nomor Telepon</label>
                                <input type="text" class="form-control" name="recipient_phone_num"
                                    id="recipient_phone_num" placeholder="Masukkan nomor telepon penerima pesanan">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Metode Pembayaran</label>
                            <select class="form-control" id="payment_method" name="payment_method">
                                <option value="">Pilih metode pembayaran</option>
                                @foreach ($paymentMethod as $pay)
                                    <option value="{{ $pay->id_detail_configuration }}">{{ $pay->name }}</option>
                                @endforeach
                            </select>
                        </div>
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
                        <button type="submit" class="btn btn-primary" onclick="getNota($sales->id_sales)"
                            id="btnCetakNota" disabled>Cetak
                            Nota</button>
                    </div>
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
                            <p>Jenis Diskon:</p>
                            
                            @foreach ($discountUmum as $dsc)
                                @if (!str_contains($dsc->name, 'Diskon Musim'))
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input"
                                            id="radio{{ $dsc->id_detail_configuration }}" name="optradio"
                                        value="{{ $dsc->id_detail_configuration }}" data-name="{{ $dsc->name }}"
                                            data-value="{{ $dsc->value }}" onclick="updateDiscountValue(this)">
                                        <label class="form-check-label"
                                            for="radio{{ $dsc->id_detail_configuration }}">{{ $dsc->name }}
                                            ({{ $dsc->value }}%)
                                        </label>
                                    </div>
                                @endif
                            @endforeach

                            {{-- Pilihan Diskon Musim --}}
                            @foreach ($groupedSeasonDiscounts as $dsc)
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="radioSeason{{ $dsc['id'] }}"
                                        name="optradio" value="{{ $dsc['id'] }}" data-name="{{ $dsc['name'] }}"
                                        data-jenis="{{ $dsc['jenis'] }}" onclick="updateDiscountValue(this)">
                                    <label class="form-check-label" for="radioSeason{{ $dsc['id'] }}">
                                        Diskon Musim
                                    </label>
                                </div>
                                {{-- Card untuk Diskon Musim --}}
                                <div class="p-3 bg-light rounded">
                                    <strong>{{ $dsc['name'] }}</strong><br>
                                    {{ date('d-m-Y',strtotime($dsc['start_date'])) }} sampai {{ date('d-m-Y',strtotime($dsc['end_date'])) }}<br>

                                    @foreach ($dsc['categories'] as $category)
                                        {{ $category['category_name'] }} Diskon {{ $category['season_value'] }}%<br>
                                    @endforeach
                                </div>
                            @endforeach

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

        function formatDate(input) {
            if (input.value) {
                let date = new Date(input.value);
                let day = String(date.getDate()).padStart(2, '0');
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let year = date.getFullYear();

                // Tampilkan ke user dalam format DD/MM/YYYY
                input.type = 'text';
                input.value = `${day}/${month}/${year}`;

                // Simpan ke input hidden dalam format YYYY-MM-DD untuk database
                document.getElementById('dateHidden').value = `${year}-${month}-${day}`;
                if (document.getElementById('text-dikirim').style.display !== "none") {
                    document.getElementById('delivery_date').value = `${year}-${month}-${day}`;
                }
            } else {
                input.type = 'text';
                document.getElementById('dateHidden').value = '';
                document.getElementById('delivery_date').value = '';
            }
        }


        document.addEventListener('DOMContentLoaded', () => {
            const btnCetakNota = document.getElementById('btnCetakNota');
            const radioYes = document.getElementById('radioYes');
            const radioNo = document.getElementById('radioNo');
            // const radioButtons = document.querySelectorAll('input[name="radioPayment"]');

            if (!btnCetakNota || !radioYes || !radioNo) {
                console.error("Element tidak ditemukan!");
                return;
            }
            // Fungsi untuk mengupdate status tombol
            function updateButtonState() {
                console.log('Radio "Ya" checked:', radioYes.checked); // Debugging
                btnCetakNota.disabled = !radioYes.checked;
            }
            // Tambahkan event listener untuk setiap radio button
            // radioButtons.forEach((radio) => {
            //     radio.addEventListener('change', updateButtonState);
            // });
            radioYes.addEventListener('change', updateButtonState);
            radioNo.addEventListener('change', updateButtonState);
            // Memanggil toggleDiscountButton saat halaman dimuat untuk memastikan tombol dalam kondisi yang tepat
            toggleDiscountButton();

            // Menambahkan event listener untuk setiap radio button
            document.querySelectorAll('input[name="optradio"]').forEach((radio) => {
                radio.addEventListener('change', function() {
                    toggleDiscountButton
                        (); // Memperbarui status tombol setelah memilih jenis diskon
                });
            });

            const radioDikirim = document.getElementById('dikirim');
            const textDikirim = document.getElementById('text-dikirim');

            function updateModalContent() {
                if (radioDikirim.checked) {
                    textDikirim.style.display = "block";
                }
            }

            radioDikirim.addEventListener('change', updateModalContent);
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


        function addProduct() {
            const productSelect = document.getElementById("productName");
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const productName = selectedOption.text;
            const productPrice = parseFloat(document.getElementById("productPrice").value) || 0;
            const productQty = parseInt(document.getElementById("productQty").value) || 1;
            const totalStock = parseInt(document.getElementById("total_stock").value) || 0;
            const metodePengiriman = document.querySelector('input[name="metode_pengiriman"]:checked')?.value || null;
            console.log('a' + metodePengiriman);
            const categoryId = selectedOption.getAttribute("data-categoryId");
            // console.log("Category ID:", categoryId);


            let productAmount = updateAmount();
            console.log("Debug -> Adding Product:", {
                productName,
                productPrice,
                productQty,
                productAmount,
                categoryId
            });
            if (!productName || productPrice <= 0 || productQty <= 0) {
                alert("Please select a product and provide valid values!");
                return;
            }
            if (metodePengiriman == 'diambil') {
                if (productQty > totalStock) {
                    alert(
                        "Kuantitas melebihi total stok, harap atur kuantitas maksimum sesuai dengan total kuantitas stok!"
                    );
                    return;
                }
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
                categoryId: categoryId
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
            <td>Biaya Pengiriman</td>
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
            // Ambil elemen input diskon
            const discountInput = document.getElementById('discount');

            // Jika tidak ada input diskon, buat input tersembunyi
            if (!discountInput) {
                const hiddenDiscountInput = document.createElement('input');
                hiddenDiscountInput.type = 'hidden';
                hiddenDiscountInput.id = 'discount';
                hiddenDiscountInput.name = 'discount';
                document.querySelector('form').appendChild(hiddenDiscountInput);
            }

            // Ambil informasi diskon dari radio button
            const discountName = radio.getAttribute('data-name');
            const discountId = radio.value;

            // Untuk diskon umum
            const generalDiscounts = @json($discountUmum);
            const seasonDiscounts = @json($groupedSeasonDiscounts);

            let discountValue = null;

            // Cari diskon umum
            const generalDiscount = generalDiscounts.find(
                dsc => dsc.id_detail_configuration.toString() === discountId
            );

            if (generalDiscount) {
                discountValue = parseFloat(generalDiscount.value);
                document.getElementById('discount').value = discountValue;

                console.log('Diskon Umum Dipilih:', {
                    nama: discountName,
                    nilai: discountValue
                });
            }
            // Untuk diskon musim
            else {
                const selectedSeasonDiscount = seasonDiscounts.find(
                    discount => discount.id.toString() === discountId
                );
                console.log('select', selectedSeasonDiscount);

                if (selectedSeasonDiscount) {
                    // Siapkan array untuk menyimpan kategori dan diskon
                    const categoryDiscounts = selectedSeasonDiscount.categories.map(category => ({
                        category_id: category.category_id,
                        category_name: category.category_name,
                        discount_value: parseFloat(category.season_value)
                    }));

                    // Simpan data diskon musim ke input
                    document.getElementById('discount').value = JSON.stringify(categoryDiscounts);

                    console.log('Diskon Musim Dipilih:', {
                        nama: discountName,
                        id: discountId,
                        kategori: categoryDiscounts
                    });
                }
            }

            // Perbarui status tombol diskon
            toggleDiscountButton();
        }

        function getSelectedDiscountName() {
            const radios = document.getElementsByName('optradio');
            console.log('get select diskon', radios);

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
            // Ambil radio button yang dipilih
            const selectedDiscountRadio = document.querySelector('input[name="optradio"]:checked');

            if (!selectedDiscountRadio) {
                alert('Silakan pilih jenis diskon terlebih dahulu.');
                return;
            }

            const discountName = selectedDiscountRadio.getAttribute('data-name');
            console.log('name', discountName);

            const jenisDiskon = selectedDiscountRadio.getAttribute('data-jenis') || '';
            console.log('jenis', jenisDiskon);

            const discountId = selectedDiscountRadio.value;
            const discountHidden = document.getElementById('hDiscount');

            // Ambil data produk yang sudah ditambahkan
            const hiddenProductsInput = document.getElementById("hidden_products_input");
            let currentProducts = JSON.parse(hiddenProductsInput.value || '[]');

            // Variabel untuk tracking diskon
            let totalDiscount = 0;
            let discountedProducts = [];

            // Calculate total amount (if not already defined)
            totalAmount = currentProducts.reduce((sum, product) => sum + product.amount, 0);
            console.log('tt', totalAmount);


            console.log('Current Products:', JSON.stringify(currentProducts));

            // Jika ini diskon musim, cari produk yang sesuai kategori
            if (jenisDiskon.includes('Diskon Musim')) {
                // Ambil data diskon musim dari attribute radio button
                const seasonDiscounts = @json($groupedSeasonDiscounts);
                const selectedSeasonDiscount = seasonDiscounts.find(discount =>
                    discount.id.toString() === discountId
                );
                console.log('selected season dis', selectedSeasonDiscount);

                if (selectedSeasonDiscount) {
                    // Loop produk yang sudah ditambahkan
                    currentProducts = currentProducts.map(product => {
                        // Debug: Log product category and discount categories
                        console.log('Full Product Object:', JSON.stringify(product));
                        console.log('Product Category:', product.categoryId);
                        console.log('Discount Categories:', selectedSeasonDiscount.categories);

                        // Cari kategori produk di diskon musim
                        const categoryDiscount = selectedSeasonDiscount.categories.find(
                            cat => {
                                console.log('Comparing:',
                                    cat.category_name,
                                    'with',
                                    product.category_name
                                );
                                // Try matching by category name if ID is undefined
                                return (
                                    (cat.category_id && cat.category_id.toString() === product
                                        .categoryId) ||
                                    (cat.category_name && cat.category_name === product.category_name)
                                );
                            }
                        );

                        if (categoryDiscount) {
                            // Hitung diskon untuk produk ini
                            const discountPercentage = parseFloat(categoryDiscount.season_value);
                            const productDiscount = product.amount * (discountPercentage / 100);

                            // Tambahkan info diskon ke produk
                            product.discount = {
                                percentage: discountPercentage,
                                amount: productDiscount
                            };

                            totalDiscount += productDiscount;
                            discountedProducts.push({
                                name: product.name,
                                originalAmount: product.amount,
                                discountAmount: productDiscount,
                                discountPercentage: discountPercentage
                            });

                            // Debug: Log discount details
                            console.log('Discount Applied:', {
                                productName: product.name,
                                discountPercentage: discountPercentage,
                                productDiscount: productDiscount
                            });
                            product.amount -= productDiscount;
                        }
                        return product;
                    });

                    // Perbarui hidden input dengan produk yang sudah dimodifikasi
                    hiddenProductsInput.value = JSON.stringify(currentProducts);
                }
            } else {
                // Untuk diskon non-musim, gunakan logika sebelumnya
                const discountValue = parseFloat(selectedDiscountRadio.getAttribute('data-value') || 0);

                // Ensure we have a valid discount value
                if (discountValue > 0) {
                    totalDiscount = totalAmount * (discountValue / 100);
                    totalDiscount = Math.min(totalDiscount, totalAmount);

                    // Add the discount to discounted products
                    discountedProducts.push({
                        name: discountName,
                        originalAmount: totalAmount,
                        discountAmount: totalDiscount,
                        discountPercentage: discountValue
                    });
                }
            }
            // Update totalAmount setelah diskon diterapkan
            totalAmount -= totalDiscount;
            console.log('Total setelah diskon:', totalAmount);

            // Tambahkan baris diskon ke tabel
            const tableBody = document.getElementById('productsTable');
            const row = document.createElement('tr');
            row.innerHTML = `
        <td>${discountName}</td>
        <td>-</td>
        <td>-</td>
        <td>-${totalDiscount.toFixed(2)}</td>
        <td>
            <button class="btn btn-danger btn-sm" onclick="removeRow(this)">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </td>
    `;
            tableBody.appendChild(row);

            // Debug: Tampilkan produk yang didiskon
            console.log('Produk yang didiskon:', discountedProducts);

            // // Perbarui total amount dan hidden input
            // totalAmount -= totalDiscount;
            // discountHidden.value = (parseFloat(discountHidden.value || 0) + totalDiscount).toFixed(2);

            // // Tutup modal dan update total
            // $('#modalDiscount').modal('hide');
            // updateTotals();

            // // Non-aktifkan tombol diskon jika tidak diperbolehkan memilih beberapa diskon
            // toggleDiscountButton();
            // / Update totalAmount di tampilan
            document.getElementById("totalAmount").innerText = totalAmount.toFixed(2);

            // Update hidden input untuk diskon
            discountHidden.value = (parseFloat(discountHidden.value || 0) + totalDiscount).toFixed(2);

            // Tutup modal dan update total lainnya
            $('#modalDiscount').modal('hide');
            updateTotals();
            toggleDiscountButton();
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
