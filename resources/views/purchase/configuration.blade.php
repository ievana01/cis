@extends('layouts.btemplate')
@section('content')
    @if (@session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <form action="{{ route('purchase.configuration.save') }}" method="POST">
        @csrf
        <a href="#modalVerifikasi" class="btn btn-success" data-toggle="modal">Simpan</a>
        <a href="/" class="btn btn-danger">Batal</a>

        @foreach ($configuration as $c)
            <div class="card-body bg-white mt-2 mb-2">
                <h5 class="card-title">{{ $c->name }}</h5>
                <p>{{ $c->description }}</p>
                <ul>
                    @foreach ($c->details as $detail)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input"
                                id="checkbox{{ $detail->id_detail_configuration }}"
                                name="configurations[{{ $c->id_configuration }}][]"
                                value="{{ $detail->id_detail_configuration }}"
                                {{ $detail->status_active == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="checkbox{{ $detail->id_detail_configuration }}">
                                {{ $detail->name }}
                            </label>
                            <p>{{ $detail->description }}</p>
                        </div>
                    @endforeach
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
        })
    </script>
@endsection
