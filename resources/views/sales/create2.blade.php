@extends('layouts.btemplate')
@section('content')
    <div class="container mt-5">
        <h2 class="text-center">Nota Penjualan</h2>
        <form method="POST" action="{{ route('sales.store') }}">
            @csrf
            <div class="mb-3">
                <label for="sales_invoice">Invoice Number</label>
                <input type="text" class="form-control" id="sales_invoice" name="sales_invoice" value="{{ $invoiceNumber }}"
                    readonly>
            </div>
            <div class="mb-3">
                <label for="id_customer">Pembeli</label>
                <select class="form-control" id="id_customer" name="id_customer">
                    @foreach ($customer as $c)
                        <option value="{{ $c->id_customer }}"> {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="created_at">Tanggal Order</label>
                <input type="date" class="form-control" name="created_at" aria-describedby="created_at">
            </div>

            <div id="rincianBarang">
                <h5>Rincian Barang</h5>
                <div class="row g-3 align-items-center mb-3" id="barangRow">
                    <div class="col-md-3">
                        <select class="form-control product-select" name="barang[]">
                            <option value="">Pilih produk</option>
                            @foreach ($product as $p)
                                <option value="{{ $p->id_product }}" data-price="{{ $p->price }}">{{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="jumlah[]" class="form-control" placeholder="Jumlah" min="1"
                            value="1" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="harga[]" class="form-control" placeholder="Harga Satuan"
                            required readonly>
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="total_price[]" class="form-control" placeholder="Total per item"
                            required readonly>
                    </div>

                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm" onclick="hapusBarang(this)">Hapus</button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-primary mb-3" onclick="tambahBarang()">Tambah Barang</button>

            <!-- Total Harga -->
            <div class="mb-3">
                <label for="totalHarga" class="form-label">Total</label>
                <input type="number" id="totalHarga" name="totalHarga" class="form-control" placeholder="Total Harga"
                    readonly>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-success">Simpan Nota</button>
        </form>
    </div>
@endsection

@section('javascript')
    <script>
        // Fungsi untuk menambah baris barang baru
        function tambahBarang() {
            // Menyalin elemen baris pertama yang ada pada template
            const row = document.querySelector("#barangRow");
            const newRow = row.cloneNode(true); // Salin semua elemen dalam template

            // Resetkan nilai input pada baris baru
            const inputs = newRow.querySelectorAll("input");
            inputs.forEach(input => {
                input.value = ''; // Mengosongkan nilai input
            });

            // Mengatur jumlah untuk baris baru menjadi 1
            const jumlahInput = newRow.querySelector('input[name="jumlah[]"]');
            jumlahInput.value = 1; // Set nilai jumlah ke 1

            // Menambahkan baris baru ke dalam div rincianBarang
            document.querySelector("#rincianBarang").appendChild(newRow);

            // Menambahkan event listener untuk dropdown produk pada baris baru
            const newSelect = newRow.querySelector('.product-select');
            newSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                const priceInput = this.closest('.row').querySelector('[name="harga[]"]');
                priceInput.value = price;
                hitungTotalHarga(); // Hitung ulang total harga
            });

            // Memastikan harga untuk produk pertama juga terisi jika ada
            const firstSelect = newRow.querySelector('.product-select');
            if (firstSelect.value) {
                const selectedOption = firstSelect.options[firstSelect.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                const priceInput = firstSelect.closest('.row').querySelector('[name="harga[]"]');
                priceInput.value = price;
            }

            // Menambahkan event listener untuk menghitung total harga setelah jumlah atau harga satuan diubah
            const jumlahInputBaru = newRow.querySelector('input[name="jumlah[]"]');
            const hargaInputBaru = newRow.querySelector('input[name="harga[]"]');
            const totalPriceInputBaru = newRow.querySelector('input[name="total_price[]"]');

            jumlahInputBaru.addEventListener('input', hitungTotalHarga);
            hargaInputBaru.addEventListener('input', hitungTotalHarga);

            function hitungTotalHarga() {
                const jumlah = parseInt(jumlahInputBaru.value) || 0;
                const hargaSatuan = parseFloat(hargaInputBaru.value) || 0;
                const totalHarga = jumlah * hargaSatuan;
                totalPriceInputBaru.value = totalHarga;

                // Menghitung total harga keseluruhan
                let totalHargaKeseluruhan = 0;
                document.querySelectorAll('input[name="total_price[]"]').forEach(function(input) {
                    totalHargaKeseluruhan += parseFloat(input.value) || 0;
                });
                document.getElementById('totalHarga').value = totalHargaKeseluruhan;
            }
        }

        // Fungsi untuk menghapus baris barang
        function hapusBarang(button) {
            const row = button.closest('.row');
            if (document.querySelectorAll('.row').length > 1) { // Pastikan minimal 1 baris tetap ada
                row.remove();
            }
            hitungTotalHarga(); // Hitung ulang total harga setelah baris dihapus
        }

        // Menangani perubahan pada dropdown produk untuk mengambil harga produk
        document.addEventListener('DOMContentLoaded', function() {
            const productSelects = document.querySelectorAll('.product-select');
            productSelects.forEach(select => {
                select.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const price = selectedOption.getAttribute('data-price');
                    const priceInput = this.closest('.row').querySelector('[name="harga[]"]');
                    priceInput.value = price;
                    hitungTotalHarga(); // Hitung ulang total harga
                });
            });
        });
    </script>
@endsection
