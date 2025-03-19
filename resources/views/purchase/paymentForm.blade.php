<form method="POST" action="{{ route('purchase.update', $purchase->id_purchase) }}">
    @csrf
    @method('PUT')
    
    <input type="hidden" name="id_purchase" value="{{ $purchase->id_purchase }}">

    <p>No Ref : {{ $purchase->purchase_invoice }}</p>
    <p>Tanggal Order : {{ date('d-m-Y', strtotime($purchase->date)) }}</p>

    <p>Total Pembayaran : Rp. {{ number_format($purchase->total_purchase, 0, ',', '.') }}</p>

    <label for="payment_method">Metode Pembayaran</label>
    <select class="form-control" name="payment_method" id="payment_method">
        <option value="">Pilih Metode Pembayaran</option>
        @foreach ($paymentMethod as $pay)
            <option value="{{ $pay->id_detail_configuration }}">{{ $pay->name }}</option>
        @endforeach
    </select>

    <button type="submit" class="btn btn-success mt-2">Bayar</button>
</form>
