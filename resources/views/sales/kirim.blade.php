@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <h4 class="font-weight-bold">Kirim Produk</h4>
    <div>
        <p><strong>Nomor Ref:</strong> {{ $sales->sales_invoice }}</p>
        <p><strong>Tanggal Order:</strong> {{ date('d-m-Y', strtotime($sales->date)) }}</p>
        <p><strong>Pelanggan:</strong> {{ $sales->customer_name_by_id ?? $sales->customer_name }}</p>
        <p><strong>Staf:</strong> {{ $sales->e_name }}</p>
        @if ($sales->customer_name_by_id  == 'Umum')
            <p><strong>Nama Penerima:</strong> {{ $sales->recipient_name ?? '-' }} </p>
            <p><strong>Alamat Penerima:</strong> {{ $sales->recipient_address ?? '-' }} </p>
            <p><strong>Nomor Telepon Penerima:</strong> {{ $sales->recipient_phone_num ?? '-' }} </p>
        @endif
    </div>
    @php
        $semuaSudahDikirim = true;
    @endphp

    @foreach ($salesDetail as $sd)
        @php
            $terkirimProduk = $terkirim->where('product_id', $sd->product_id)->sum('terkirim');
            $sisa = $sd->total_quantity - $terkirimProduk;
            if ($sisa > 0) {
                $semuaSudahDikirim = false;
            }
        @endphp
    @endforeach
    @if (!$semuaSudahDikirim)
        <form action="{{ route('delivery-note.store') }}" method="POST">
            @csrf
            <input type="hidden" name="sales_id" value="{{ $sales->id_sales }}">
            <div>
                <label for="date">Tanggal Kirim</label>
                <input type="text" class="form-control" name="date" id="dateInput" placeholder="Silahkan pilih tanggal"
                    onfocus="this.type='date'" onblur="formatDate(this)">
                <input type="hidden" name="date" id="dateHidden">
            </div>
            <div class="mt-2 mb-2">
                <a href="#modalInfo" class="btn btn-info" data-toggle="modal">Info Stok Produk</a>
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
                    <tr>
                        @foreach ($salesDetail as $index => $sd)
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $sd->product_name }}</td>
                            {{-- Cari jumlah terkirim berdasarkan product_id --}}
                            @php
                                $terkirimProduk = $terkirim->where('product_id', $sd->product_id)->sum('terkirim');
                                $sisa = $sd->total_quantity - $terkirimProduk;
                            @endphp

                            <td>{{ $sisa }}</td>
                            <td>
                                <input class="form-control" type="number"
                                    name="products[{{ $index }}][total_quantity]" min="1" value="1"
                                    required>
                                <input type="hidden" name="products[{{ $index }}][id_product]"
                                    value="{{ $sd->product_id }}">
                            </td>
                        @endforeach
                        <td class="d-flex align-items-center">
                            <select class="form-control" id="warehouse_id_in" name="warehouse_id_in">
                                <option value="">Pilih Gudang</option>
                                @foreach ($gudang as $g)
                                    <option value="{{ $g->id_warehouse }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Kirim</button>
            <a href="{{ route('sales.index') }}" class="btn btn-danger">Batal</a>
        </form>
    @endif
    @if ($semuaSudahDikirim)
        <p class="text-danger font-weight-bold text-center">PRODUK TELAH DIKIRIM SEMUANYA</p>
        <a href="{{ route('sales.index') }}" class="btn btn-danger">Batal</a>
    @endif
    <div class="modal fade" id="modalInfo" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-body" id="modalContent">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Total Stok</th>
                                <th>Lokasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stokProd as $sp)
                                <tr>
                                    <th>{{ $sp->product_name }}</th>
                                    <th>{{ $sp->stock }}</th>
                                    <th>{{ $sp->name }}</th>
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

            } else {
                input.type = 'text';
                document.getElementById('dateHidden').value = '';

            }
        }
    </script>
@endsection
