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
                        @if ($c->id_configuration == 5 || $c->id_configuration == 6)
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

                                @if (str_contains($detail->name, 'Diskon Musim'))
                                    <a href="#modalSetting" class="btn btn-info" data-toggle="modal"
                                        onclick="getDiskon({{ $detail->id_detail_configuration }})">Setting Diskon Musim</a>
                                @endif

                                {{-- nampilin input untuk value diskon --}}
                                @if (str_contains($detail->name, 'Diskon') &&
                                        !str_contains($detail->name, 'Multi Diskon') &&
                                        !str_contains($detail->name, 'Diskon Musim'))
                                    <div class="pb-2">
                                        <label for="name">Masukkan besar diskon:</label>
                                        <input type="number" class="form-control" id="value"
                                            name="discount_values[{{ $detail->id_detail_configuration }}]"
                                            placeholder="Masukkan besaran nilai diskon" value="{{ $detail->value }}">
                                    </div>
                                @endif
                            </div>
                        @endif
                        {{-- custom pajak -- form input tambahan untuk id_configuration = 2 dan id_detail_configuration = 4 --}}
                    @endforeach
                    @if ($c->id_configuration == 6 && $detail->id_detail_configuration == 16)
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


        <div class="modal fade" id="modalSetting" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body" id="modalContentSetting">
                        <div class="form-group">

                        </div>
                    </div>
                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const targetRadio = document.getElementById('radio16');
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

            document.querySelectorAll('input[name="verifikasi"]').forEach((radio) => {
                radio.addEventListener('change', updateButtonState);
            });

        });

        function formatDate(input) {
            if (input.value) {
                let date = new Date(input.value);
                let day = String(date.getDate()).padStart(2, '0');
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let year = date.getFullYear();

                input.type = 'text';
                input.value = `${day}/${month}/${year}`;

                document.getElementById('start_date').value = `${year}-${month}-${day}`;
                document.getElementById('end_date').value = `${year}-${month}-${day}`;
            } else {
                input.type = 'text';
                document.getElementById('start_date').value = '';
                document.getElementById('end_date').value = '';
            }
        }

        //     function addDiscount() {
        //         let categorySelect = document.querySelector("#category_id");
        //         let seasonInput = document.querySelector("input[name='season_value[]']");

        //         let categoryValue = categorySelect.value;
        //         let categoryText = categorySelect.options[categorySelect.selectedIndex].text;
        //         let seasonValue = seasonInput ? seasonInput.value.trim() : "";

        //         if (!categoryValue || seasonValue === "") {
        //             alert("Harap pilih kategori dan masukkan besar diskon!");
        //             return;
        //         }

        //         let tableBody = document.getElementById("discountTableBody");
        //         let newRow = tableBody.insertRow();

        //         newRow.innerHTML = `
    //     <td>
    //         <input type="hidden" name="category_id[]" value="${categoryValue}">
    //         ${categoryText}
    //     </td>
    //     <td>
    //         <input type="hidden" name="season_value[]" value="${seasonValue}">
    //         ${seasonValue}%
    //     </td>
    //     <td>
    //         <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button>
    //     </td>
    // `;

        //         // Reset input setelah tambah
        //         categorySelect.selectedIndex = 0;
        //         seasonInput.value = "";
        //     }


        //     function removeRow(button) {
        //         let row = button.parentNode.parentNode;
        //         row.parentNode.removeChild(row);
        //     }

        function addDiscount() {
            let categorySelect = document.querySelector(".category-select");
            let seasonInput = document.querySelector(".discount-input");

            let categoryValue = categorySelect.value;
            let categoryText = categorySelect.options[categorySelect.selectedIndex].text;
            let seasonValue = seasonInput.value.trim();

            if (!categoryValue || seasonValue === "") {
                alert("Harap pilih kategori dan masukkan besar diskon!");
                return;
            }

            let tableBody = document.getElementById("discountTableBody");
            let newRow = document.createElement('tr');

            newRow.innerHTML = `
                    <td>
                        <input type="hidden" name="category_id[${categoryValue}]" value="${categoryValue}">
                        ${categoryText}
                    </td>
                    <td>
                        <input type="hidden" name="season_value[${categoryValue}]" value="${seasonValue}">
                        ${seasonValue}%
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button>
                    </td>
                `;

            tableBody.appendChild(newRow);

            categorySelect.selectedIndex = 0;
            seasonInput.value = "";
        }

        window.removeRow = function(button) {
            button.closest('tr').remove();
        };

        function getDiskon(id_detail_configuration) {
            console.log("ID yang dikirim:", id_detail_configuration); 
            $.ajax({
                type: "POST",
                url: "{{ route('sales.getDiskon') }}",
                data: {
                    '_token': '<?php echo csrf_token(); ?>',
                    'id': id_detail_configuration
                },
                success: function(data) {
                    $('#modalContentSetting').html(data.msg)
                }
            });
        }
    </script>
@endsection
