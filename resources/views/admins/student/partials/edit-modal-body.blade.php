<div class="modal-header border-0 pb-0 pt-6 px-7 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-3">
        <div class="symbol symbol-40px symbol-circle bg-light-primary me-1">
            <span class="symbol-label text-primary fw-bold">
                <i class="fa fa-user-edit fs-4 text-primary"></i>
            </span>
        </div>
        <div>
            <h3 class="modal-title fw-bolder text-gray-900 fs-4 mb-0">
                {{ isset($student) ? 'Edit Data Siswa' : 'Tambah Data Siswa' }}
            </h3>
            <span class="text-slate-400 text-[12px]">Perbarui informasi profil & akademik santri</span>
        </div>
    </div>
    <button type="button" class="btn btn-icon btn-sm btn-active-light-primary rounded-circle" data-bs-dismiss="modal" aria-label="Close">
        <i class="fa fa-times fs-5 text-gray-500"></i>
    </button>
</div>

<form id="form-edit-student-modal"
      action="{{ isset($student) ? route('student.update', $student->id) : route('student.store') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($student))
        <input type="hidden" name="_method" value="PUT">
    @endif

    <div class="modal-body pt-5 px-7">
        <div class="row g-6">
            <!-- Left Column -->
            <div class="col-md-4">
                <!-- Avatar Upload -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label mb-2">Foto Siswa</label>
                    <div class="d-flex align-items-center gap-4">
                        <div class="image-input image-input-outline" data-kt-image-input="true">
                            <div class="image-input-wrapper w-90px h-90px"
                                 style="background-image: url('{{ $student->avatar_url ?? asset('assets/media/avatars/default.png') }}');"></div>
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                   data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Ubah Avatar">
                                <i class="bi bi-pencil-fill fs-7"></i>
                                <input type="file" name="avatar" accept=".png, .jpg, .jpeg" />
                            </label>
                        </div>
                        <span class="text-slate-400 text-[11px]">Format: png, jpg, jpeg.<br>Maks: 2MB</span>
                    </div>
                </div>

                <!-- Wali Siswa -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label required" for="modal_user_id">Nama Wali Siswa</label>
                    <x-form.user :value="@$student->user_id" id="modal_user_id" class="form-select form-select-solid" />
                </div>

                <!-- Jenis Kelamin -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label required" for="modal_gender">Jenis Kelamin</label>
                    <select name="gender" class="form-select form-select-solid" id="modal_gender" required>
                        <option value="L" {{ @$student->gender == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                        <option value="P" {{ @$student->gender == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <!-- Tempat Lahir -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label required" for="modal_born_place">Tempat Lahir</label>
                    <input type="text" class="form-control form-control-solid" id="modal_born_place"
                           name="born_place" placeholder="Masukkan Tempat Lahir"
                           value="{{ @$student->born_place ?? old('born_place') }}" required />
                </div>

                <!-- Status -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label required" for="modal_status">Status Siswa</label>
                    <select name="status" class="form-select form-select-solid" id="modal_status" required>
                        <option value="ACTIVE" {{ @$student->status == "ACTIVE" ? 'selected' : '' }}>Aktif</option>
                        <option value="INACTIVE" {{ @$student->status == "INACTIVE" ? 'selected' : '' }}>Tidak Aktif</option>
                        <option value="GRADUATED" {{ @$student->status == "GRADUATED" ? 'selected' : '' }}>Lulus</option>
                        <option value="DROPPED_OUT" {{ @$student->status == "DROPPED_OUT" ? 'selected' : '' }}>Keluar / Drop Out</option>
                        <option value="TRANSFERRED" {{ @$student->status == "TRANSFERRED" ? 'selected' : '' }}>Pindah</option>
                    </select>
                </div>
            </div>

            <!-- Middle Column -->
            <div class="col-md-4">
                <!-- Nama Siswa -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label required" for="modal_name">Nama Lengkap Siswa</label>
                    <input type="text" class="form-control form-control-solid" id="modal_name" name="name"
                           placeholder="Masukkan Nama Siswa" value="{{ @$student->name ?? old('name') }}" required />
                </div>

                <!-- Nickname -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label" for="modal_nickname">Nama Panggilan</label>
                    <input type="text" class="form-control form-control-solid" id="modal_nickname" name="nickname"
                           placeholder="Masukkan Nama Panggilan" value="{{ @$student->nickname ?? old('nickname') }}" />
                </div>

                <!-- Tanggal Lahir -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label required" for="modal_birth_date">Tanggal Lahir</label>
                    <input type="date" class="form-control form-control-solid" id="modal_birth_date"
                           name="birth_date" value="{{ @$student->birth_date ?? old('birth_date') }}" required />
                </div>

                <!-- NISN -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label" for="modal_nisn">NISN (Nomor Induk Siswa Nasional)</label>
                    <input type="text" class="form-control form-control-solid" id="modal_nisn" name="nisn"
                           placeholder="Masukkan NISN" value="{{ @$student->nisn ?? old('nisn') }}" />
                </div>

                <!-- NIS -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label required" for="modal_nis">NIS (Nomor Induk Santri)</label>
                    <input type="text" class="form-control form-control-solid" id="modal_nis" name="nis"
                           placeholder="Masukkan NIS" value="{{ @$student->nis ?? old('nis') }}" required />
                </div>

                <!-- UPT / Sekolah -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label required" for="modal_school_id">Sekolah / UPT</label>
                    <select name="school_id" class="form-select form-select-solid" id="modal_school_id" required>
                        <option value="">Pilih Sekolah</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" {{ @$student->school_id == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-4">
                <!-- Kelas (3 Kolom Grid Single Select) -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label required" for="modal_classroom_id">Pilih Kelas</label>
                    <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
                        <button type="button" id="modal_btn_toggle_classroom" class="btn btn-sm btn-light-primary text-start flex-grow-1 d-flex justify-content-between align-items-center">
                            <span id="selected_class_name">{{ @$student->classroom->name ?? 'Pilih Kelas' }}</span>
                            <i class="fas fa-chevron-down fs-7"></i>
                        </button>
                    </div>

                    <!-- Hidden Input for Form Submission -->
                    <input type="hidden" name="classroom_id" id="modal_classroom_id" value="{{ @$student->classroom_id }}" required />

                    <!-- 3-Column Grid Container -->
                    <div class="card card-bordered bg-light p-2.5 rounded-[16px] border-gray-300 d-none mt-2" id="classroom_3col_card" style="max-height: 240px; overflow-y: auto;">
                        <div class="row g-2" id="classroom_3col_grid">
                            <!-- Populated dynamically via render3ColClassrooms -->
                        </div>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label" for="modal_address">Alamat Lengkap</label>
                    <textarea class="form-control form-control-solid" id="modal_address" name="address"
                              rows="2" placeholder="Masukkan Alamat">{{ @$student->address ?? old('address') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="fv-row mb-5">
                            <label class="fs-6 fw-bold form-label" for="modal_city">Kota</label>
                            <input type="text" class="form-control form-control-solid" id="modal_city" name="city"
                                   placeholder="Kota" value="{{ @$student->city ?? old('city') }}" />
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="fv-row mb-5">
                            <label class="fs-6 fw-bold form-label" for="modal_province">Provinsi</label>
                            <input type="text" class="form-control form-control-solid" id="modal_province" name="province"
                                   placeholder="Provinsi" value="{{ @$student->province ?? old('province') }}" />
                        </div>
                    </div>
                </div>

                <!-- Penanggung Jawab Kamar (Auto-fill, Readonly) -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label text-slate-700">Penanggung Jawab Kamar</label>
                    <input type="text" class="form-control form-control-solid bg-light text-slate-600"
                           value="{{ @$student->asramaHost->name ?? (@$student->asrama?->hostAdmin?->name ?? '-') }}"
                           readonly disabled />
                    <span class="form-text text-muted text-[11px]">Terisi otomatis dari Data Asrama</span>
                </div>

                <!-- Nama Kamar (Auto-fill, Readonly) -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label text-slate-700">Nama Kamar</label>
                    <input type="text" class="form-control form-control-solid bg-light text-slate-600"
                           value="{{ @$student->asrama_name ?? (@$student->asrama?->name ?? '-') }}"
                           readonly disabled />
                    <span class="form-text text-muted text-[11px]">Terisi otomatis dari Data Asrama</span>
                </div>
                
                <!-- Action Buttons moved to the third column -->
                <div class="d-flex justify-content-end gap-2 mt-8">
                    <button type="button" class="btn btn-light rounded-[24px] px-6" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-[24px] px-6" id="btn-save-edit-student">
                        <i class="fa fa-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    (function() {
        // Initialize Select2 for Wali Santri in modal
        var $modalUserSelect = $('#modal_user_id');
        if ($modalUserSelect.length) {
            if ($modalUserSelect.data('select2')) {
                $modalUserSelect.select2('destroy');
            }
            $modalUserSelect.select2({
                dropdownParent: $('#modalEditSiswa').length ? $('#modalEditSiswa') : $modalUserSelect.closest('.modal'),
                placeholder: "Pilih Wali Siswa",
                allowClear: true,
                ajax: {
                    url: "{{ route('select2') }}",
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {
                            search: params.term,
                            data_type: "USER"
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                var statusText = 'Non Jamaah';
                                if (item.jamaah_status === 'JAMAAH') {
                                    statusText = 'Jamaah';
                                } else if (item.jamaah_status === 'MUKIMIN') {
                                    statusText = 'Mukimin';
                                }
                                var phoneText = item.phone ? ' - ' + item.phone : '';
                                return {
                                    text: item.name + ' [' + statusText + ']' + phoneText,
                                    id: item.id
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
        }

        // 3-Column Classroom Renderer Engine
        function render3ColClassrooms(classroomList, currentSelectedId) {
            var $grid = $('#classroom_3col_grid');
            $grid.empty();

            if (!classroomList || classroomList.length === 0) {
                $grid.html('<div class="col-12 text-center py-4 text-muted fs-7"><i class="fas fa-info-circle me-1"></i> Tidak ada kelas tersedia untuk sekolah ini</div>');
                $('#selected_class_name').text('Pilih Kelas');
                $('#modal_classroom_id').val('');
                return;
            }

            // Group classrooms by Grade Prefix
            var gradeMap = {};
            $.each(classroomList, function(i, c) {
                var name = $.trim(c.name);
                var match = name.match(/^(VII|VIII|IX|X{1,2}I{0,2}|I{1,3}V?|[0-9]+)/i);
                var gradeKey = 'Lainnya';
                if (match) {
                    var g = match[1].toUpperCase();
                    if (g === '7' || g === 'VII') gradeKey = 'Kelas 7';
                    else if (g === '8' || g === 'VIII') gradeKey = 'Kelas 8';
                    else if (g === '9' || g === 'IX') gradeKey = 'Kelas 9';
                    else if (g === '10' || g === 'X') gradeKey = 'Kelas 10 / X';
                    else if (g === '11' || g === 'XI') gradeKey = 'Kelas 11 / XI';
                    else if (g === '12' || g === 'XII') gradeKey = 'Kelas 12 / XII';
                    else gradeKey = 'Tingkat ' + g;
                }
                if (!gradeMap[gradeKey]) gradeMap[gradeKey] = [];
                gradeMap[gradeKey].push(c);
            });

            var keys = Object.keys(gradeMap);
            var cols = [];

            if (keys.length === 3) {
                // Natural 3 grade levels (e.g. Kelas 7, 8, 9)
                cols = [
                    { title: keys[0], items: gradeMap[keys[0]] },
                    { title: keys[1], items: gradeMap[keys[1]] },
                    { title: keys[2], items: gradeMap[keys[2]] }
                ];
            } else if (keys.length > 3) {
                // > 3 groups -> chunk into 3 balanced columns
                var total = classroomList.length;
                var chunkSize = Math.ceil(total / 3);
                cols = [
                    { title: 'Tingkat I', items: classroomList.slice(0, chunkSize) },
                    { title: 'Tingkat II', items: classroomList.slice(chunkSize, chunkSize * 2) },
                    { title: 'Tingkat III', items: classroomList.slice(chunkSize * 2) }
                ];
            } else {
                // 1 or 2 groups -> chunk evenly into 3 columns
                var total = classroomList.length;
                var chunkSize = Math.ceil(total / 3);
                cols = [
                    { title: 'Kolom 1', items: classroomList.slice(0, chunkSize) },
                    { title: 'Kolom 2', items: classroomList.slice(chunkSize, chunkSize * 2) },
                    { title: 'Kolom 3', items: classroomList.slice(chunkSize * 2) }
                ];
            }

            var selectedFound = false;

            // Render 3 Columns
            $.each(cols, function(colIdx, col) {
                if (!col.items || col.items.length === 0) return;

                var colHtml = '<div class="col-4">';
                colHtml += '  <div class="bg-white p-2 rounded-[12px] border border-gray-200 shadow-xs h-100">';
                colHtml += '    <div class="text-center fw-bolder text-primary fs-8 uppercase pb-1 mb-2 border-bottom border-gray-200">' + col.title + '</div>';
                colHtml += '    <div class="d-flex flex-column gap-1">';

                $.each(col.items, function(idx, item) {
                    var isSelected = (String(item.id) === String(currentSelectedId));
                    if (isSelected) selectedFound = true;

                    colHtml += '      <button type="button" class="btn btn-sm btn-classroom-opt text-start d-flex align-items-center justify-content-between py-1.5 px-2 rounded-[8px] transition-all fs-8 fw-bold ' +
                        (isSelected ? 'btn-primary text-white shadow-xs' : 'btn-light-secondary text-gray-800 hover-elevate-up border border-gray-200') +
                        '" data-id="' + item.id + '" data-name="' + item.name + '">';
                    colHtml += '        <span class="truncate">' + item.name + '</span>';
                    colHtml += '        <i class="fas fa-check-circle fs-9 text-white ' + (isSelected ? '' : 'd-none') + '"></i>';
                    colHtml += '      </button>';
                });

                colHtml += '    </div>';
                colHtml += '  </div>';
                colHtml += '</div>';

                $grid.append(colHtml);
            });

            // Update selected badge
            if (selectedFound) {
                var activeBtn = $grid.find('.btn-classroom-opt.btn-primary');
                if (activeBtn.length) {
                    $('#selected_class_name').text(activeBtn.data('name'));
                    $('#modal_classroom_id').val(activeBtn.data('id'));
                }
            } else if (currentSelectedId) {
                $('#modal_classroom_id').val(currentSelectedId);
            }
        }

        // Toggle 3-col Grid
        $('#modal_btn_toggle_classroom').on('click', function(e) {
            e.preventDefault();
            $('#classroom_3col_card').toggleClass('d-none');
        });

        // Handle button click for single select
        $(document).off('click', '.btn-classroom-opt').on('click', '.btn-classroom-opt', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');

            $('#modal_classroom_id').val(id).trigger('change');
            $('#selected_class_name').text(name);

            $('#classroom_3col_grid .btn-classroom-opt')
                .removeClass('btn-primary text-white shadow-xs')
                .addClass('btn-light-secondary text-gray-800 hover-elevate-up border border-gray-200');
            $('#classroom_3col_grid .btn-classroom-opt i.fa-check-circle').addClass('d-none');

            $(this).removeClass('btn-light-secondary text-gray-800 border-gray-200')
                .addClass('btn-primary text-white shadow-xs');
            $(this).find('i.fa-check-circle').removeClass('d-none');

            // Hide the grid after selection
            $('#classroom_3col_card').addClass('d-none');
        });

        $('#modal_school_id').on('change', function () {
            var school_id = $(this).val();
            if (school_id) {
                $.ajax({
                    url: '{{ route("select2") }}',
                    type: "GET",
                    data: {
                        school_id: school_id,
                        data_type: 'CLASSROOM_BY_SCHOOL'
                    },
                    dataType: "json",
                    success: function (data) {
                        render3ColClassrooms(data, $('#modal_classroom_id').val());
                    }
                });
            } else {
                render3ColClassrooms([], '');
            }
        });

        // Initialize immediately on load
        var initialClassrooms = @json($classrooms ?? []);
        var currentClassroomId = '{{ @$student->classroom_id }}';
        if (initialClassrooms && initialClassrooms.length > 0) {
            render3ColClassrooms(initialClassrooms, currentClassroomId);
        } else if ($('#modal_school_id').val()) {
            $('#modal_school_id').trigger('change');
        }

        // Auto-nickname logic
        var nicknameInput = document.getElementById('modal_nickname');
        var nameInput = document.getElementById('modal_name');
        if (nicknameInput && nameInput) {
            var nicknameTouched = nicknameInput.value.trim() !== '';
            nicknameInput.addEventListener('input', function() { nicknameTouched = nicknameInput.value.trim() !== ''; });
            nameInput.addEventListener('input', function() {
                if (!nicknameTouched) {
                    var full = nameInput.value.trim();
                    nicknameInput.value = full ? full.split(' ')[0] : '';
                }
            });
        }
    })();
</script>
