@extends('layouts.blank')
@section('content')
    <form method="POST" action="{{ route('purchase.store') }}">
        @csrf
        <h4 class="text-center">Purchase Order</h4>
        <table class="table table-condensed">
            <tbody>
                <tr>
                    <th><label for="purchase_invoice">Purchase Invoice</label></th>
                    <th><input type="text" readonly class="form-control" id="purchase_invoice" value="{{ $invoiceNumber }}">
                    </th>
                </tr>
                <tr>
                    <th><label for="id_supplier">Supplier</label></th>
                    <th>
                        <select class="form-control" id="id_supplier" name="id_supplier">
                            <option value="">Choose Supplier</option>
                            @foreach ($supplier as $s)
                                <option value="{{ $s->id_supplier }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </th>
                </tr>
                <tr>
                    <th><label for="date">Order Date</label></th>
                    <th><input type="date" class="form-control" name="date"></th>
                </tr>
                <tr>
                    <th><label for="warehouse">Location Stock</label></th>
                    <th>
                        <select class="form-control" name="id_warehouse" id="id_warehouse">
                            <option value="">Choose warehouse</option>
                            @foreach ($warehouse as $w)
                                <option value="{{ $w->id_warehouse }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </th>
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
                    <th>Option</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select class="form-control" id="productName" onchange="updateCost()">
                            <option value="">Select a product</option>
                            @foreach ($product as $p)
                                <option value="{{ $p->id_product }}" data-cost="{{ $p->cost ?? 0 }}">
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" id="productCost" class="form-control" readonly>
                    </td>
                    <td>
                        <input type="number" id="productQty" class="form-control" min="1" value="1">
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
                    <th><i class="fa-solid fa-trash-can"></i></th>
                </tr>
            </thead>
            <tbody id="productsTable"></tbody>
        </table>
        <input type="hidden" name="total_price" id="total_price_input" value="0">
        <div style="text-align: right;">
            <p>Total Amount: <b id="totalAmount">Rp 0.00</b></p>
            <p>Taxes: <b id="taxes">Rp 0.00</b></p>
            <p>Total: <b id="total_price">Rp 0.00</b></p>
        </div>
        <div style="text-align: right;">
            <a href="{{ route('purchase.index') }}" type="button" class="btn btn-danger">Cancel</a>

            <button type="submit" class="btn btn-primary"> Create</button>
        </div>
    </form>
@endsection

@section('javascript')
    <script>
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
            const taxRate = 0.1;
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
            console.log(qty);

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
