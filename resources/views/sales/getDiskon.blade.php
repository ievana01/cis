{{-- @extends('layouts.blank')
@section('content') --}}
<h4 class="font-weight-bold">Daftar Diskon Musim</h4>
<a href="#modalAdd" class="btn btn-primary mb-2" data-toggle="modal">Tambah Diskon</a>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Kode Diskon</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Akhir</th>
            <th>Daftar Kategori</th>
        </tr>
    </thead>
    <tbody>
        @forelse($diskonMusim as $ds)
            <tr>
                <td>{{ $ds['name'] }}</td>
                <td>{{ $ds['start_date'] }}</td>
                <td>{{ $ds['end_date'] }}</td>
                <td>
                    @if (!empty($ds['categories']) && $ds['categories'])
                        <ul class="list-unstyled">
                            @foreach ($ds['categories'] as $cat)
                                <li>
                                    {{ $cat['category_name'] }} (Diskon: {{ $cat['season_value'] }}%)
                                    <button class="btn btn-danger btn-sm delete-category"
                                        data-id="{{ $cat['category_id'] }}" data-discount="{{ $ds['id'] }}">
                                        Hapus
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        Tidak ada kategori
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">Data tidak ditemukan</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="modal fade" id="modalAdd" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body" id="modalContentSetting">
                <div class="form-group">
                    <form action="{{ route('seasonDiscount.store') }}" method="POST">
                        @csrf
                        <div class="d-flex mt-2">
                            <div class="mr-4">
                                <label for="start_date">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date" id="start_date">
                            </div>
                            <div>
                                <label for="end_date">Tanggal Akhir</label>
                                <input type="date" class="form-control" name="end_date" id="end_date">
                            </div>
                        </div>
                        <div class="pt-2 mr-4">
                            <label for="name">Masukkan kode:</label>
                            <input type="text" class="form-control mb-2" id="name" name="name"
                                placeholder="Kode diskon musim">
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Besar Diskon</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select class="form-control category-select">
                                            <option value="">Pilih kategori</option>
                                            @foreach ($category as $c)
                                                <option value="{{ $c->id_category }}">
                                                    {{ $c->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control discount-input" min="1">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary add-discount"
                                            onclick="addDiscount()">+</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Besar Diskon</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="discountTableBody">
                            </tbody>
                        </table>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>
{{-- @endsection
@section('javascript') --}}
<script>
    $(document).on('click', '.delete-category', function() {
        let categoryId = $(this).data('id');
        console.log(categoryId);
        
        let discountId = $(this).data('discount');

        if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
            $.ajax({
                url: '/hapus-kategori-diskon', // Sesuaikan dengan route di Laravel
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    category_id: categoryId,
                    discount_id: discountId
                },
                success: function(response) {
                    alert(response.message);
                    location.reload(); // Refresh halaman setelah hapus
                },
                error: function(xhr) {
                    alert('Gagal menghapus kategori.');
                }
            });
        }
    });
</script>
{{-- @endsection --}}
