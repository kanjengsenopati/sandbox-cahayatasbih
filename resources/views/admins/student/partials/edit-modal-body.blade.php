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
            <div class="col-md-6">
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

                <!-- Penanggung Jawab Kamar (Auto-fill, Readonly) -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label text-slate-700">Penanggung Jawab / Ustadz Kamar</label>
                    <input type="text" class="form-control form-control-solid bg-light text-slate-600"
                           value="{{ @$student->asramaHost->name ?? (@$student->asrama?->hostAdmin?->name ?? '-') }}"
                           readonly disabled />
                    <span class="form-text text-muted text-[11px]">Terisi otomatis dari Data Asrama (Single Source of Truth)</span>
                </div>

                <!-- Nama Kamar (Auto-fill, Readonly) -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label text-slate-700">Nama Kamar</label>
                    <input type="text" class="form-control form-control-solid bg-light text-slate-600"
                           value="{{ @$student->asrama_name ?? (@$student->asrama?->name ?? '-') }}"
                           readonly disabled />
                    <span class="form-text text-muted text-[11px]">Terisi otomatis dari Data Asrama (Single Source of Truth)</span>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
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

                <!-- Kelas -->
                <div class="fv-row mb-5">
                    <label class="fs-6 fw-bold form-label required" for="modal_classroom_id">Kelas</label>
                    <select name="classroom_id" class="form-select form-select-solid" id="modal_classroom_id" required>
                        <option value="{{ @$student->classroom_id }}" selected>
                            {{ @$student->classroom->name ?? 'Pilih Kelas' }}
                        </option>
                    </select>
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
                            <label class="fs-6 fw-bold form-label" for="modal_city">Kota / Kabupaten</label>
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
            </div>
        </div>
    </div>

    <div class="modal-footer border-0 pt-0 px-7 pb-6 d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-light rounded-[24px] px-6" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary rounded-[24px] px-6" id="btn-save-edit-student">
            <i class="fa fa-save me-1"></i> Simpan Perubahan
        </button>
    </div>
</form>

<script>
    (function() {
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
                        $('#modal_classroom_id').empty();
                        if (data.length == 0) {
                            $('#modal_classroom_id').append('<option value="" selected disabled>Tidak ada kelas</option>');
                        } else {
                            $.each(data, function (key, value) {
                                $('#modal_classroom_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                    }
                });
            } else {
                $('#modal_classroom_id').empty();
            }
        });

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
