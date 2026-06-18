@extends('layouts.master', ['title' => 'Pemetaan Biometrik'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Pemetaan Biometrik</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Pengaturan</li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Pemetaan Biometrik</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-xxl">
            @include('admins.partials.tabs-aplikasi')
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <div class="card-title"></div>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-mapping">
                        <i class="fa-solid fa-plus me-1"></i> Tambah Pemetaan
                    </button>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table id="table-biometric-mapping" class="table align-middle table-row-dashed">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Nama Pengguna</th>
                                    <th>Tipe Pengguna</th>
                                    <th>Jenis Biometrik</th>
                                    <th>PIN Perangkat</th>
                                    <th>Status Template</th>
                                    <th class="text-center min-w-100px" style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Pemetaan -->
<div class="modal fade" id="modal-add-mapping" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bolder">Tambah Pemetaan Biometrik Baru</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal" id="btn-close-modal">
                    <span class="svg-icon svg-icon-1">
                        <i class="fa-solid fa-xmark fs-2"></i>
                    </span>
                </div>
            </div>
            <form id="form-add-mapping">
                @csrf
                <div class="modal-body py-10 px-lg-17">
                    
                    <div class="row g-9 mb-5">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-bold mb-2">Tipe Pengguna</label>
                            <select class="form-select form-select-solid" name="user_type" id="select-user-type" required>
                                <option value="">Pilih Tipe</option>
                                <option value="siswa">Siswa / Santri</option>
                                <option value="karyawan">Karyawan / Guru / Kasir</option>
                                <option value="user">User / Wali Santri</option>
                            </select>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-bold mb-2">Pengguna</label>
                            <select class="form-select form-select-solid" name="user_id" id="select-user-id" required disabled>
                                <option value="">Pilih Pengguna</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-9 mb-5">
                        <div class="col-md-12 fv-row">
                            <label class="required fs-6 fw-bold mb-2">Jenis Biometrik</label>
                            <select class="form-select form-select-solid" name="biometric_type" id="select-biometric-type" required>
                                <option value="fingerprint" selected>Sidik Jari (PIN Perangkat Hardware)</option>
                                <option value="face">Wajah (Webcam / Kamera HP)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Input PIN Fingerprint -->
                    <div id="section-fingerprint" class="fv-row mb-5">
                        <label class="required fs-6 fw-bold mb-2">PIN Mesin Fingerprint</label>
                        <input type="text" class="form-control form-control-solid" name="device_pin" id="input-device-pin" placeholder="Masukkan PIN Perangkat (contoh: 1002)" />
                        <span class="form-text text-muted">PIN ini harus sama dengan PIN/ID pengguna yang terdaftar di mesin fisik fingerprint (ZKTeco/dll).</span>
                    </div>

                    <!-- Kamera Scan Wajah (Webcam) -->
                    <div id="section-face" class="fv-row mb-5" style="display: none;">
                        <label class="required fs-6 fw-bold mb-2">Registrasi Wajah</label>
                        <div class="d-flex flex-column align-items-center bg-light rounded p-5">
                            
                            <!-- Status & Loading -->
                            <div id="face-status" class="alert alert-info w-100 text-center mb-3">
                                Menunggu inisialisasi modul AI wajah...
                            </div>

                            <!-- Frame Kamera -->
                            <div class="position-relative overflow-hidden border border-gray-300 rounded bg-dark" style="width: 320px; height: 240px;">
                                <video id="webcam-preview" width="320" height="240" autoplay muted playsinline class="position-absolute" style="top: 0; left: 0;"></video>
                                <canvas id="webcam-canvas" width="320" height="240" class="position-absolute" style="top: 0; left: 0; z-index: 10;"></canvas>
                            </div>

                            <!-- Tombol Aksi Kamera -->
                            <div class="d-flex gap-3 mt-4">
                                <button type="button" class="btn btn-secondary btn-sm" id="btn-start-camera" disabled>
                                    <i class="fa-solid fa-video me-1"></i> Aktifkan Kamera
                                </button>
                                <button type="button" class="btn btn-warning btn-sm" id="btn-capture-face" disabled style="display: none;">
                                    <i class="fa-solid fa-camera me-1"></i> Ambil Wajah & Ekstrak
                                </button>
                            </div>

                            <input type="hidden" name="template_data" id="input-template-data" />
                        </div>
                    </div>

                </div>

                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal" id="btn-cancel">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-mapping">
                        <span class="indicator-label">Simpan Pemetaan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- Load face-api.js modern fork -->
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>

<script>
    $(document).ready(() => {
        // Init DataTable
        var table = $('#table-biometric-mapping').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: "{{ route('biometric-mapping.index') }}",
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                }
            },
            columns: [
                {
                    "data": null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'user_name' },
                { data: 'user_type' },
                { data: 'biometric_type_label' },
                { data: 'device_pin_label' },
                { data: 'has_template' },
                { data: 'action', class: 'text-center' }
            ]
        });

        // Select2 Dynamic Search
        $('#select-user-type').on('change', function() {
            const type = $(this).val();
            const selectId = $('#select-user-id');
            
            if (!type) {
                selectId.prop('disabled', true).val('').trigger('change');
                return;
            }

            selectId.prop('disabled', false).select2({
                dropdownParent: $('#modal-add-mapping'),
                ajax: {
                    url: "{{ route('biometric-mapping.search-users') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            type: type,
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                placeholder: 'Cari nama pengguna...',
                minimumInputLength: 1
            });
        });

        // Biometric Type Toggle View
        $('#select-biometric-type').on('change', function() {
            const val = $(this).val();
            if (val === 'fingerprint') {
                $('#section-fingerprint').show();
                $('#section-face').hide();
                $('#input-device-pin').prop('required', true);
                stopWebcam();
            } else {
                $('#section-fingerprint').hide();
                $('#section-face').show();
                $('#input-device-pin').prop('required', false);
                initFaceApi();
            }
        });

        // Face Recognition Variables
        let video = document.getElementById('webcam-preview');
        let canvas = document.getElementById('webcam-canvas');
        let stream = null;
        let isFaceApiLoaded = false;
        let detectionInterval = null;

        // Init Face API models
        async function initFaceApi() {
            if (isFaceApiLoaded) {
                $('#btn-start-camera').prop('disabled', false);
                $('#face-status').removeClass('alert-info alert-danger alert-success').addClass('alert-info').text('Modul AI Siap. Silakan aktifkan kamera.');
                return;
            }

            $('#face-status').text('Mengunduh model neural net deteksi wajah (SSD Mobilenet & landmarks)...');
            try {
                // Memuat model dari CDN JSdelivr (Vektor wajah 128D)
                const modelUrl = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri(modelUrl),
                    faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl),
                    faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl)
                ]);

                isFaceApiLoaded = true;
                $('#btn-start-camera').prop('disabled', false);
                $('#face-status').removeClass('alert-info alert-danger alert-success').addClass('alert-info').text('Modul AI Siap. Silakan aktifkan kamera.');
            } catch (err) {
                console.error(err);
                $('#face-status').removeClass('alert-info alert-danger alert-success').addClass('alert-danger').text('Gagal memuat modul AI. Pastikan koneksi internet aktif.');
            }
        }

        // Kamera Aksi
        $('#btn-start-camera').on('click', async function() {
            $(this).prop('disabled', true);
            $('#face-status').text('Mengaktifkan kamera...');
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 320, height: 240, facingMode: 'user' } 
                });
                video.srcObject = stream;
                
                // Mulai deteksi wajah real-time
                video.onplay = () => {
                    $('#btn-capture-face').show().prop('disabled', false);
                    $('#face-status').text('Kamera Aktif. Posisikan wajah Anda di tengah kotak.');
                    startRealTimeDetection();
                };
            } catch (err) {
                console.error(err);
                $(this).prop('disabled', false);
                $('#face-status').removeClass('alert-info alert-danger alert-success').addClass('alert-danger').text('Gagal membuka kamera. Izinkan hak akses kamera di browser Anda.');
            }
        });

        function startRealTimeDetection() {
            const displaySize = { width: 320, height: 240 };
            faceapi.matchDimensions(canvas, displaySize);

            detectionInterval = setInterval(async () => {
                if (!stream) return;
                const detections = await faceapi.detectAllFaces(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                    .withFaceLandmarks();
                
                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                
                // Clear canvas
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                // Draw detection box
                faceapi.draw.drawDetections(canvas, resizedDetections);

                if (detections.length > 0) {
                    $('#btn-capture-face').removeClass('btn-warning').addClass('btn-success').text('Ekstrak Wajah (Terdeteksi)');
                } else {
                    $('#btn-capture-face').removeClass('btn-success').addClass('btn-warning').text('Mencari Wajah...');
                }
            }, 300);
        }

        // Ambil descriptor wajah (Embedding 128D)
        $('#btn-capture-face').on('click', async function() {
            $('#face-status').text('Mengekstrak landmark & fitur wajah (128D Embedding)...');
            $(this).prop('disabled', true);
            
            const detection = await faceapi.detectSingleFace(video)
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                $('#face-status').removeClass('alert-info alert-danger alert-success').addClass('alert-danger').text('Fitur wajah tidak terdeteksi jelas. Pastikan pencahayaan cukup.');
                $(this).prop('disabled', false);
                return;
            }

            // Simpan deskriptor (vektor float 128)
            const descriptorArray = Array.from(detection.descriptor);
            $('#input-template-data').val(JSON.stringify(descriptorArray));
            
            $('#face-status').removeClass('alert-info alert-danger alert-success').addClass('alert-success').text('Wajah berhasil diekstrak dan didaftarkan!');
            toastr.success('Foto wajah berhasil diverifikasi & diekstrak.');
            
            // Stop webcam
            stopWebcam();
        });

        function stopWebcam() {
            if (detectionInterval) {
                clearInterval(detectionInterval);
                detectionInterval = null;
            }
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            video.srcObject = null;
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            $('#btn-start-camera').prop('disabled', false).show();
            $('#btn-capture-face').hide();
        }

        // Form Submit
        $('#form-add-mapping').on('submit', function(e) {
            e.preventDefault();
            
            const bioType = $('#select-biometric-type').val();
            if (bioType === 'face' && !$('#input-template-data').val()) {
                toastr.error('Harap lakukan ekstraksi wajah terlebih dahulu!');
                return;
            }

            const btn = $('#btn-save-mapping');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "{{ route('biometric-mapping.store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: (res) => {
                    btn.prop('disabled', false).text('Simpan Pemetaan');
                    if (res.success) {
                        toastr.success(res.message);
                        $('#modal-add-mapping').modal('hide');
                        resetModal();
                        table.ajax.reload();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: (xhr) => {
                    btn.prop('disabled', false).text('Simpan Pemetaan');
                    toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan pemetaan.');
                }
            });
        });

        function resetModal() {
            stopWebcam();
            $('#form-add-mapping')[0].reset();
            $('#select-user-id').val('').trigger('change').prop('disabled', true);
            $('#select-biometric-type').val('fingerprint').trigger('change');
            $('#input-template-data').val('');
        }

        // Modal hidden listeners
        $('#modal-add-mapping').on('hidden.bs.modal', function () {
            resetModal();
        });
    });
</script>
@endpush
