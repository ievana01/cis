@extends('layouts.btemplate')

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form action="{{ route('sales.configuration.save') }}" method="POST">
        @csrf
        <a href="/" class="btn btn-danger">Batal</a>
        <button class="btn btn-success" type="submit">Simpan</button>

        @foreach ($configuration as $c)
            <div class="card-body bg-white mt-2 mb-2">
                <h5 class="card-title">{{ $c->name }}</h5>
                <p>{{ $c->description }}</p>
                <ul>
                    @foreach ($c->details as $detail)
                        {{-- metode HPP dan pajak --}}
                        @if ($c->id_configuration == 1 || $c->id_configuration == 2 || $c->id_configuration == 4)
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
        });
    </script>
@endsection
