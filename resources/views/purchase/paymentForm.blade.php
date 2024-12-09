<form method="POST" action="{{ route('purchase.update', $purchase->id_purchase) }}">
    @csrf
    @method('PUT')
    
    <input type="hidden" name="id_purchase" value="{{ $purchase->id_purchase }}">

    <p>Purchase Invoice : {{ $purchase->purchase_invoice }}</p>
    <p>Order Date : {{ $purchase->date }}</p>
    <p>Total Payment : Rp. {{ $purchase->total_purchase }}</p>

    <label for="payment_method">Payment Method</label>
    <select class="form-control" name="payment_method" id="payment_method">
        <option value="">Choose payment method</option>
        @foreach ($paymentMethod as $pay)
            <option value="{{ $pay->id_detail_configuration }}">{{ $pay->name }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-success mt-2">Submit</button>
</form>
