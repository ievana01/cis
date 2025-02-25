@extends('layouts.btemplate')

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form action="{{ route('sales.configuration.save') }}" method="POST">
        @csrf
        <a href="#modalVerifikasi" class="btn btn-success" data-toggle="modal">Simpan</a>
        <a href="/" class="btn btn-danger">Batal</a>

        @foreach ($configuration as $c)
            <div class="card-body bg-white mt-2 mb-2">
                <h5 class="card-title">{{ $c->name }}</h5>
                <p>{{ $c->description }}</p>
                <ul>
                    @foreach ($c->details as $detail)
                        {{-- metode HPP dan pajak --}}
                        @if ($c->id_configuration == 1 || $c->id_configuration == 2)
                            <div class="form-check">
                                <input type="radio" class="form-check-input"
                                    id="radio{{ $detail->id_detail_configuration }}"
                                    name="configurations[{{ $c->id_configuration }}]"
                                    value="{{ $detail->id_detail_configuration }}"
                                    {{ $detail->status_active == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="radio{{ $detail->id_detail_configuration }}">
                                    {{ $detail->name }}
                                </label>
                                <p>{{ $detail->description }}</p>
                            </div>
                        @else
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input"
                                    id="checkbox{{ $detail->id_detail_configuration }}"
                                    name="configurations[{{ $c->id_configuration }}][]"
                                    value="{{ $detail->id_detail_configuration }}"
                                    {{ $detail->status_active == 1 ? 'checked' : '' }}
                                    {{ $detail->type == 'mandatory' ? 'disabled' : '' }}>
                                <label class="form-check-label" for="checkbox{{ $detail->id_detail_configuration }}">
                                    {{ $detail->name }}
                                </label>
                                <p>{{ $detail->description }}</p>
                                {{-- nampilin input untuk value diskon --}}
                                @if (str_contains($detail->name, 'Diskon') && !str_contains($detail->name, 'Multi Diskon'))
                                    <div class="pb-2">
                                        <input type="number" class="form-control" id="value"
                                            name="discount_values[{{ $detail->id_detail_configuration }}]"
                                            placeholder="Masukkan besaran nilai diskon" value="{{ $detail->value }}">
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                    {{-- custom pajak -- form input tambahan untuk id_configuration = 2 dan id_detail_configuration = 4 --}}
                    @if ($c->id_configuration == 2 && $detail->id_detail_configuration == 4)
                        <div id="taxForm" style="display: none;">
                            <input type="number" id="value" class="form-control" name="tax_values[4]"
                                placeholder="Masukkan besaran nilai pajak (%)" value="{{ $detail->value }}">
                        </div>
                    @endif

                </ul>
            </div>
        @endforeach

        <div class="modal fade" id="modalVerifikasi" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body" id="modalContent">
                        <div class="form-group">
                            <label for="">Apakah anda yakin menyimpan konfigurasi ini?</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="radioYes" name="verifikasi"
                                    value="ya" required>
                                <label class="form-check-label" for="radioYes">Ya</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="radioNo" name="verifikasi"
                                    value="tidak">
                                <label class="form-check-label" for="radioNo">Tidak</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="btnVerifikasi" disabled>Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const targetRadio = document.getElementById('radio4');
            const additionalForm = document.getElementById('taxForm');

            // Tampilkan form tambahan jika radio sudah aktif saat halaman dimuat
            if (targetRadio && targetRadio.checked) {
                additionalForm.style.display = 'block';
            }

            // Tambahkan event listener untuk memperbarui tampilan form saat radio diubah
            if (targetRadio) {
                targetRadio.addEventListener('change', function() {
                    if (this.checked) {
                        additionalForm.style.display = 'block';
                    } else {
                        additionalForm.style.display = 'none';
                    }
                });
            }

            const btnVerifikasi = document.getElementById('btnVerifikasi');
            const radioYes = document.getElementById('radioYes');
            const radioNo = document.getElementById('radioNo');

            if (!btnVerifikasi || !radioYes || !radioNo) {
                return;
            }

            function updateButtonState() {
                btnVerifikasi.disabled = !radioYes.checked;
            }

            // radioYes.addEventListener('change', updateButtonState);
            // radioNo.addEventListener('change', updateButtonState);
            document.querySelectorAll('input[name="verifikasi"]').forEach((radio) => {
                radio.addEventListener('change', updateButtonState);
            });

        });
    </script>
@endsection
