@extends('layouts.blank')
@section('content')
    <form method="POST" action="{{ route('sales.store') }}" enctype="multipart/form-data">
        @csrf
        <h4 class="text-center">Sales Order</h4>
        <table class="table table-condensed">
            <tbody>
                <tr>
                    <th><label for="sales_invoice">Sales Invoice</label></th>
                    <th><input type="text" readonly class="form-control" id="sales_invoice" value="{{ $invoiceNumber }}">
                    </th>
                </tr>
                <tr>
                    <th><label for="id_customer">Customer</label></th>
                    <th>
                        <div class="input-group">
                            <select class="form-control" id="id_customer" name="id_customer"
                                onchange="toggleOtherCustomerInput()">
                                <option value="">Choose Customer</option>
                                @foreach ($customer as $c)
                                    <option value="{{ $c->id_customer }}">{{ $c->name }}</option>
                                @endforeach
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div id="other_customer_input" style="display: none;" class="pt-2">
                            <label for="customer_name">Enter Customer Data:</label>
                            <div class="pb-2">
                                <input type="text" class="form-control mb-2" id="customer_name" name="customer_name"
                                    placeholder="Customer Name" />
                            </div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th><label for="date">Order Date</label></th>
                    <th><input type="date" class="form-control" name="date"></th>
                </tr>
                <tr>
                    <th><label for="tax">Tax Rate</label></th>
                    <th><input type="text" class="form-control" name="tax" id="tax"
                            value="{{ $taxRate * 100 }}%" disabled></th>
                </tr>
            </tbody>
        </table>

        <input type="hidden" name="products" id="hidden_products_input" value="[]">

        <h5 class="ml-2">Product</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Stock</th>
                    <th>Option</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select class="form-control" id="productName" onchange="updatePrice()">
                            <option value="">Select a product</option>
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
                        <button type="button" class="btn btn-primary" onclick="addProduct()">Add</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <h5 class="ml-2">List Product</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th>Discount</th>
                    <th><i class="fa-solid fa-trash-can"></i></th>
                </tr>
            </thead>
            <tbody id="productsTable"></tbody>
        </table>

        {{-- <div style="text-align: right;" class="mb-4 mt-2">
            <a href="#modalShipping" class="btn btn-primary" data-toggle="modal">Add Shipping
                Cost</a>
            @if ($discount->isNotEmpty() && !$discount->contains('id_detail_configuration', 11))
                <a href="#modalDiscount" class="btn btn-success" data-toggle="modal">Add Discount</a>
            @endif
        </div> --}}
        <div style="text-align: right;" class="mb-4 mt-2">
            <a href="#modalShipping" class="btn btn-primary" data-toggle="modal">Add Shipping Cost</a>
            @if (
                $discount->isNotEmpty() &&
                    $discount->filter(function ($item) {
                            return $item->status_active == 1 && $item->id_detail_configuration != 11;
                        })->isNotEmpty())
                <a href="#modalDiscount" class="btn btn-success" data-toggle="modal">Add Discount</a>
            @endif
        </div>



        <input type="hidden" name="total_price" id="total_price_input" value="0">
        <input type="hidden" id="hShippingCost" name="hShippingCost" value="0">
        <input type="hidden" id="hDiscount" name="hDiscount" value="0">


        <div style="text-align: right;">
            <p>Total Amount: <b id="totalAmount">Rp 0.00</b></p>
            <p>Taxes: <b id="taxes">Rp 0.00</b></p>
            <p>Total: <b id="total_price">Rp 0.00</b></p>
        </div>

        <div style="text-align: right;">
            <a href="{{ route('sales.index') }}" type="button" class="btn btn-danger">Cancel</a>
            <a href="#modalPayment" class="btn btn-primary" data-toggle="modal">Create</a>
        </div>

        <div class="modal fade" id="modalPayment" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body" id="modalContent">
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select class="form-control" id="payment_method" name="payment_method">
                                <option value="">Choose Payment Method</option>
                                @foreach ($paymentMethod as $pay)
                                    <option value="{{ $pay->id_detail_configuration }}">{{ $pay->name }}</option>
                                @endforeach
                            </select>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Invoice</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalShipping" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body" id="modalContent">
                        <div class="form-group">
                            <label for="shipping_cost">Shipping Cost</label>
                            <input type="number" class="form-control" id="shipping_cost" name="shipping_cost"
                                aria-describedby="shipping_cost" placeholder="Insert shipping cost">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="submitShippingForm()">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalDiscount" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body" id="modalContent">
                        <div class="form-group">
                            <label for="discount">Discount Type:</label>
                            @foreach ($discount as $dsc)
                                @if ($dsc->id_detail_configuration != 11)
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input"
                                            id="radio{{ $dsc->id_detail_configuration }}" name="optradio"
                                            value="{{ $dsc->id_detail_configuration }}" data-name="{{ $dsc->name }}"
                                            data-value="{{ $dsc->value }}" onclick="updateDiscountValue(this)">
                                        <label class="form-check-label"
                                            for="radio{{ $dsc->id_detail_configuration }}">{{ $dsc->name }}</label>
                                    </div>
                                @endif
                            @endforeach
                            <input type="number" class="form-control" id="discount" name="discount" value=""
                                disabled style="display: none;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="submitDiscount()">Save</button>
                    </div>
                </div>
            </div>
        </div>

    </form>
@endsection

@section('javascript')
    <script>
        let totalAmount = 0;
        // const taxRate = 0.1; // 10% tax  

        function toggleOtherCustomerInput() {
            const customerSelect = document.getElementById("id_customer");
            const otherCustomerInput = document.getElementById("other_customer_input");

            if (customerSelect.value === "other") {
                otherCustomerInput.style.display = "block"; // Menampilkan form input nama customer
            } else {
                otherCustomerInput.style.display = "none"; // Menyembunyikan form input nama customer
            }
        }

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

            // Cek apakah customer dipilih
            const customerSelect = document.getElementById("id_customer");
            const selectedCustomer = customerSelect.value;
            const otherCustomerInput = document.getElementById("customer_name").value;
            console.log(otherCustomerInput);

            let discountRate = 0;
            if (selectedCustomer && selectedCustomer !== "other") {
                const discount = discounts.find(d => d.name.toLowerCase().includes('member'));
                console.log('x', discount);

                if (discount) {
                    discountRate = parseFloat(discount.value) / 100;
                }
            }

            if (selectedCustomer === "other" && otherCustomerInput) {
                discountRate = 0;
            }

            // Hitung diskon
            const discountMember = productAmount * discountRate;
            productAmount -= discountMember;
            // console.log("Debug -> Diskon:", {
            //     discountRate,
            //     discount,
            //     productAmount
            // });

            // Append to the product list table
            const productsTable = document.getElementById("productsTable");
            const newRow = `
                <tr>
                    <td>${productName}</td>
                    <td>${productPrice.toFixed(2)}</td>
                    <td>${productQty}</td>
                    <td>${productAmount.toFixed(2)}</td>
                    <td>Dsc Mem ${discountRate > 0 ? `-Rp ${discountMember.toFixed(2)}` : '-'}</td>
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
                discountMember: discountMember,
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
            <td>-</td>
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
            discountField.value = discountValue;
            discountField.style.display = 'block';
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

        function submitDiscount() {
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            console.log('diskon', discount);

            const discountName = getSelectedDiscountName();
            console.log('diskonname', discountName);

            if (discountName === '') {
                alert('Please select a discount type.');
                return;
            }

            if (discount < 0) {
                alert("Please enter a valid discount.");
                return;
            }

            let finalDiscount = discount;
            console.log('final discount', finalDiscount);

            if (discountName == 'Discount Percentage') {
                finalDiscount = totalAmount * (discount / 100);
                console.log('final diskon', finalDiscount);
            } else if (discountName == 'Seasonal Discount' || discountName == 'Discount Order') {
                finalDiscount = discount;
            }

            const tableBody = document.getElementById('productsTable');
            const row = document.createElement('tr');

            row.innerHTML = `
            <td>${discountName}</td>
            <td>-</td>
            <td>1</td>
            <td>-</td>
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
