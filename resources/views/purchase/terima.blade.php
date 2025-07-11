@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">Terima Produk</h4>
    <div>
        <p><strong>Nomor Ref:</strong> {{ $purchase->purchase_invoice }}</p>
        <p><strong>Tanggal Order:</strong> {{ date('d-m-Y', strtotime($purchase->date)) }}</p>
        <p><strong>Pemasok:</strong> {{ $purchase->supplier_name }}</p>
        <p><strong>Staf:</strong> {{ $purchase->e_name }}</p>
        <p><strong>Lokasi Terima Produk:</strong> {{ $purchase->warehouse_name }}</p>
    </div>
    @php
        $semuaSudahDiterima = true;
    @endphp
    <form action="{{ route('delivery-note.storeTerima') }}" method="POST">
        @csrf
        <input type="hidden" name="purchase_id" value="{{ $purchase->id_purchase }}">
        <input type="hidden" name="warehouse_id" value="{{ $purchase->warehouse_id }}">
        <div class="mb-2">
            <label for="date">Tanggal Terima</label>
            <input type="text" class="form-control" name="date" id="dateInput" placeholder="Silahkan pilih tanggal"
                onfocus="this.type='date'" onblur="formatDate(this)" required>
            <input type="hidden" name="date" id="dateHidden">
        </div>

        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Jumlah Diterima</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchaseDetail as $index => $pd)
                    @php
                        $terimaProduk = $terima->firstWhere('product_id', $pd->product_id);
                        $totalTerima = $terimaProduk ? $terimaProduk->total_terima : 0;
                        $sisa = $pd->total_quantity_product - $totalTerima;
                        if ($sisa > 0) {
                            $semuaSudahDiterima = false;
                        }
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $pd->product_name }}</td>
                        <td>{{ $sisa }}</td>
                        <td>
                            <input class="form-control" type="number" name="products[{{ $index }}][total_quantity]"
                                min="0" value="0" required>
                            <input type="hidden" name="products[{{ $index }}][id_product]"
                                value="{{ $pd->product_id }}">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if (!$semuaSudahDiterima)
            <button type="submit" class="btn btn-primary">Terima</button>
            <a href="{{ route('purchase.index') }}" class="btn btn-danger">Batal</a>
        @else
            <p class="text-danger font-weight-bold text-center">PRODUK TELAH DITERIMA SEMUANYA</p>
            <a href="{{ route('purchase.index') }}" class="btn btn-danger">Batal</a>
        @endif
    </form>
@endsection

@section('javascript')
    <script>
        function formatDate(input) {
            if (input.value) {
                let date = new Date(input.value);
                let day = String(date.getDate()).padStart(2, '0');
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let year = date.getFullYear();

                input.type = 'text';
                input.value = `${day}/${month}/${year}`;
                document.getElementById('dateHidden').value = `${year}-${month}-${day}`;
            } else {
                input.type = 'text';
                document.getElementById('dateHidden').value = '';
            }
        }
    </script>
@endsection
