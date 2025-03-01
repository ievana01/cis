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
    </div>
    @php
        $semuaSudahDiterima = true;
    @endphp
    @foreach ($purchaseDetail as $pd)
        @php
            $terimaProduk = $terima->where('product_id', $pd->product_id)->sum('terima');
            $sisa = $pd->total_quantity_product - $terimaProduk;
            if ($sisa > 0) {
                $semuaSudahDiterima = false;
            }
        @endphp
    @endforeach
    @if (!$semuaSudahDiterima)
        <form action="{{ route('delivery-note.storeTerima') }}" method="POST">
            @csrf
            <input type="hidden" name="purchase_id" value="{{ $purchase->id_purchase }}">
            <div class="mb-2">
                <label for="date">Tanggal Terima</label>
                <input type="text" class="form-control" name="date" id="dateInput" placeholder="Silahkan pilih tanggal"
                    onfocus="this.type='date'" onblur="formatDate(this)">
                <input type="hidden" name="date" id="dateHidden">
            </div>

            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Jumlah</th>
                        <th>Jumlah dikirim</th>
                        <th>Dari Gudang</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($purchaseDetail as $index => $pd)
                        @php
                            $terimaProduk = $terima->where('product_id', $pd->product_id)->sum('terima');
                            $sisa = $pd->total_quantity_product - $terimaProduk;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $pd->product_name }}</td>
                            <td>{{ $sisa }}</td>
                            <td>
                                <input class="form-control" type="number"
                                    name="products[{{ $index }}][total_quantity]" min="1" value="1"
                                    required {{ $sisa == 0 ? 'disabled' : '' }}>
                                <input type="hidden" name="products[{{ $index }}][id_product]"
                                    value="{{ $pd->product_id }}">
                            </td>
                            <td>
                                <select class="form-control" id="warehouse_id_in" name="warehouse_id_in">
                                    <option value="">Pilih Gudang</option>
                                    @foreach ($gudang as $g)
                                        <option value="{{ $g->id_warehouse }}">{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Terima</button>
            <a href="{{ route('purchase.index') }}" class="btn btn-danger">Batal</a>
        </form>
    @endif
    @if ($semuaSudahDiterima)
        <p class="text-danger font-weight-bold text-center">PRODUK TELAH DITERIMA SEMUANYA</p>
        <a href="{{ route('purchase.index') }}" class="btn btn-danger">Batal</a>
    @endif
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
