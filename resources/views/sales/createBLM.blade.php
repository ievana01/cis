@extends('layouts.blank')
@section('content')
    <form method="POST" action="{{ route('sales.store') }}">
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
                        <select class="form-control" id="id_customer" name="id_customer">
                            <option value="">Choose Customer</option>
                            @foreach ($customer as $c)
                                <option value="{{ $c->id_customer }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </th>
                </tr>
                <tr>
                    <th><label for="date">Order Date</label></th>
                    <th><input type="date" class="form-control" name="date"></th>
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
                        <select class="form-control" id="productName" onchange="updatePrice()">
                            <option value="">Select a product</option>
                            @foreach ($product as $p)
                                <option value="{{ $p->id_product }}" data-price="{{ $p->price }}">
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

        <div style="text-align: right;" class="mb-4 mt-2">
            <a href="#modalShipping" class="btn btn-primary" data-toggle="modal">Add Shipping
                Cost</a>
            <a href="#modalDiscount" class="btn btn-success" data-toggle="modal">Add Discount</a>
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
                            <label for="payment_method_id">Payment Method</label>
                            <select class="form-control" id="payment_method_id" name="payment_method_id">
                                <option value="">Choose Payment Method</option>
                                @foreach ($payment as $p)
                                    <option value="{{ $p->id_payment_method }}">{{ $p->name }}</option>
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
                            <label for="discount">Discount</label>
                            <input type="number" class="form-control" id="discount" name="discount"
                                aria-describedby="discount" placeholder="Insert discount">
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

        function updatePrice() {
            const productSelect = document.getElementById("productName");
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute("data-price")) || 0;

            console.log("Debug -> Selected Price:", price); // Debug log

            // Set price to input field
            document.getElementById("productPrice").value = price.toFixed(2);
        }

        function addProduct() {
            var productId = $('#productName').val();
            var productQty = parseFloat($("#productQty").val()) || 1;
            
            $.ajax({
                url: "{{ route('sales.addProduct') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    product_id: productId,
                    quantity: productQty
                },
                success: function(response) {
                    const {
                        product,
                        quantity,
                        amount
                    } = response;

                    const newRow = `
                <tr>
                    <td>${product.name}</td>
                    <td>${product.price.toFixed(2)}</td>
                    <td>${quantity}</td>
                    <td>${amount.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
            `;
                    $("#productsTable").append(newRow);

                    // Update total amount
                    totalAmount += amount;
                    updateTotals();
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.error || "Failed to add product.");
                }
            });
        }

        function submitShippingForm() {
            var shippingCost = parseFloat($("#shipping_cost").val()) || 0;

            $("#hShippingCost").val(shippingCost);
            console.log("Shipping cost updated:", $("#hShippingCost").val());

            $.ajax({
                url: "{{ route('sales.addShippingCost') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    shipping_cost: shippingCost
                },
                success: function(response) {
                    const {
                        shipping_cost
                    } = response;

                    const newRow = `
                <tr>
                    <td>Shipping Cost</td>
                    <td>-</td>
                    <td>-</td>
                    <td>${shipping_cost}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
            `;
                    $("#productsTable").append(newRow);
                    $('#modalShipping').modal('hide');
                    updateTotals();
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.error || "Failed to add shipping cost.");
                }
            });
        }

        function submitDiscount() {
            const discount = parseFloat($("#discount").val()) || 0;
            $("#hDiscount").val(discount);
            $.ajax({
                url: "{{ route('sales.addDiscount') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    discount: discount
                },
                success: function(response) {

                    const newRow = `
                <tr>
                    <td>Discount</td>
                    <td>-</td>
                    <td>-</td>
                    <td>${discount}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
            `;
                    $("#productsTable").append(newRow);
                    $('#modalDiscount').modal('hide');
                    updateTotals();
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.error || "Failed to add discount.");
                }
            });
        }

        function updateTotals() {
            const totalAmount = parseFloat($("#totalAmount").val());
            const shippingCost = parseFloat($("#hShippingCost").val());
            const discount = parseFloat($("#hDiscount").val());

            console.log("Debug -> Data dikirim:", {
                totalAmount,
                shippingCost,
                discount
            }); // Debug log

            $.ajax({
                url: "{{ route('sales.calculateTotal') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    totalAmount: totalAmount,
                    shippingCost: shippingCost,
                    discount: discount
                },
                success: function(response) {
                    console.log("Debug -> Response:", response);
                    $("#totalAmount").text(`Rp ${response.subtotal.toFixed(2)}`);
                    $("#taxes").text(`Rp ${response.taxes.toFixed(2)}`);
                    $("#total_price").text(`Rp ${response.total.toFixed(2)}`);
                    $("#total_price_input").val(response.total.toFixed(2));
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.error || "Failed to calculate totals.");
                }
            });
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
