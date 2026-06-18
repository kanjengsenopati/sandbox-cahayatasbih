@extends('layouts.master', ['title' => 'Kiosk Presensi Wajah'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Kiosk Presensi Wajah</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Kiosk Presensi Wajah</li>
                </ul>
            </div>
            
            <!-- Tombol Mode Kiosk Fullscreen -->
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-light-primary" onclick="toggleFullscreen()">
                    <i class="fa-solid fa-expand me-1"></i> Fullscreen Kiosk
                </button>
            </div>
        </div>
    </div>

    <div class="post d-flex flex-column-fluid" id="kiosk-container-full">
        <div id="kt_content_container" class="container-xxl">
            
            <div class="row g-5">
                <!-- Sektor Kamera Scanner (Kiri) -->
                <div class="col-xl-6 col-lg-7">
                    <div class="card card-flush shadow-sm bg-dark border-0 h-100" style="min-height: 480px;">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center p-8">
                            
                            <!-- Toggle Mode Presensi (Masuk / Keluar) -->
                            <div class="d-flex bg-secondary bg-opacity-10 rounded p-1 mb-6" style="width: 280px; z-index: 20;">
                                <button type="button" class="btn btn-sm btn-active-primary w-50 fw-bolder text-center px-4 btn-mode-toggle active" data-mode="in">
                                    <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> MASUK
                                </button>
                                <button type="button" class="btn btn-sm btn-active-danger w-50 fw-bolder text-center px-4 btn-mode-toggle" data-mode="out">
                                    <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> KELUAR
                                </button>
                            </div>

                            <!-- Frame Deteksi Wajah -->
                            <div class="position-relative overflow-hidden border border-gray-600 rounded-3 shadow-lg bg-black" style="width: 480px; height: 360px; max-width: 100%;">
                                <video id="kiosk-webcam" width="480" height="360" autoplay muted playsinline class="position-absolute" style="top: 0; left: 0; transform: scaleX(-1); object-fit: cover;"></video>
                                <canvas id="kiosk-canvas" width="480" height="360" class="position-absolute" style="top: 0; left: 0; z-index: 10; transform: scaleX(-1);"></canvas>
                                
                                <!-- Loading overlay -->
                                <div id="kiosk-overlay" class="position-absolute w-100 h-100 bg-black bg-opacity-75 d-flex flex-column align-items-center justify-content-center text-white" style="top: 0; left: 0; z-index: 15;">
                                    <span class="spinner-border text-primary spinner-border-lg mb-3"></span>
                                    <span id="kiosk-overlay-text" class="fw-bold fs-6">Memuat modul AI wajah...</span>
                                </div>
                            </div>

                            <div id="scanner-indicator" class="mt-4 text-gray-400 fw-bold fs-7 text-uppercase tracking-wider">
                                MENUNGGU KAMERA...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sektor Hasil Scan (Kanan) -->
                <div class="col-xl-6 col-lg-5">
                    <div class="card card-flush shadow-sm h-100" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center p-10 text-center">
                            
                            <div id="result-idle">
                                <i class="fa-solid fa-circle-user text-gray-300" style="font-size: 100px;"></i>
                                <h2 class="fw-bolder text-gray-700 mt-6 fs-1">Silakan Menghadap Kamera</h2>
                                <p class="text-gray-500 fs-5 mt-2">Dekatkan wajah Anda ke kamera untuk melakukan check-in / check-out presensi harian secara mandiri.</p>
                            </div>

                            <div id="result-success" style="display: none;">
                                <div class="position-relative mb-5 d-inline-block">
                                    <img id="result-photo" src="" class="rounded-circle border border-5 border-success shadow-lg" style="width: 140px; height: 140px; object-fit: cover;" />
                                    <span class="position-absolute bottom-0 end-0 bg-success rounded-circle p-2 shadow">
                                        <i class="fa-solid fa-check text-white fs-3"></i>
                                    </span>
                                </div>
                                <h3 id="result-name" class="fw-bolder text-slate-900 fs-1 mb-1">-</h3>
                                <span id="result-type" class="badge badge-light-success fs-6 fw-bold mb-4">-</span>
                                
                                <div class="separator separator-dashed my-4"></div>
                                
                                <div class="row w-100 mt-2">
                                    <div class="col-6 text-end">
                                        <span class="text-gray-500 fw-bold">Jam:</span>
                                    </div>
                                    <div class="col-6 text-start">
                                        <span id="result-time" class="text-gray-800 fw-bolder fs-5">-</span>
                                    </div>
                                </div>
                                
                                <div class="row w-100 mt-2">
                                    <div class="col-6 text-end">
                                        <span class="text-gray-500 fw-bold">Status:</span>
                                    </div>
                                    <div class="col-6 text-start">
                                        <span id="result-status" class="text-success fw-bolder fs-5">-</span>
                                    </div>
                                </div>

                                <div id="result-delay" class="alert alert-light-success mt-6 mb-0 py-2 px-4 fs-7 fw-bold">
                                    Menyinkronkan data presensi...
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
<script>
    let stream = null;
    let video = document.getElementById('kiosk-webcam');
    let canvas = document.getElementById('kiosk-canvas');
    let kioskMode = 'in'; // default check-in
    let isProcessing = false;
    let enrolledDescriptors = [];
    let detectionInterval = null;
    let faceMatcher = null;

    $(document).ready(() => {
        // Toggle check-in / check-out
        $('.btn-mode-toggle').on('click', function() {
            $('.btn-mode-toggle').removeClass('active');
            $(this).addClass('active');
            kioskMode = $(this).data('mode');
            
            if (kioskMode === 'in') {
                $(this).parent().removeClass('bg-danger bg-opacity-10').addClass('bg-primary bg-opacity-10');
            } else {
                $(this).parent().removeClass('bg-primary bg-opacity-10').addClass('bg-danger bg-opacity-10');
            }
        });

        // Load models and descriptors
        initKiosk();
    });

    async function initKiosk() {
        try {
            // 1. Load Models
            const modelUrl = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
            updateOverlayText('Mengunduh model neural net AI...');
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(modelUrl),
                faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl),
                faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl)
            ]);

            // 2. Fetch Enrolled Descriptors
            updateOverlayText('Sinkronisasi database wajah...');
            const res = await $.ajax({
                url: "{{ route('biometric-mapping.descriptors') }}",
                type: "GET",
                dataType: "json"
            });

            enrolledDescriptors = res;
            
            if (enrolledDescriptors.length === 0) {
                updateOverlayText('Tidak ada database wajah terdaftar. Hubungkan wajah pengguna terlebih dahulu.');
                return;
            }

            // Create LabeledFaceDescriptors for FaceMatcher
            const labeledDescriptors = enrolledDescriptors.map(item => {
                // Convert descriptor array back to Float32Array
                const floatArray = new Float32Array(item.descriptor);
                return new faceapi.LabeledFaceDescriptors(item.id, [floatArray]);
            });

            // Set up Face Matcher with threshold 0.5 (smaller threshold means stricter matching)
            faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.5);

            // 3. Start Camera
            updateOverlayText('Membuka video stream...');
            startCamera();

        } catch (err) {
            console.error(err);
            updateOverlayText('Inisialisasi kiosk gagal. Periksa koneksi internet atau kamera.');
        }
    }

    function updateOverlayText(text) {
        $('#kiosk-overlay-text').text(text);
    }

    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { width: 480, height: 360, facingMode: 'user' } 
            });
            video.srcObject = stream;
            video.onplay = () => {
                $('#kiosk-overlay').fadeOut();
                $('#scanner-indicator').text('PEMINDAIAN AKTIF...');
                startRealTimeScanning();
            };
        } catch (err) {
            console.error(err);
            updateOverlayText('Kamera tidak ditemukan / Akses ditolak.');
        }
    }

    function startRealTimeScanning() {
        const displaySize = { width: 480, height: 360 };
        faceapi.matchDimensions(canvas, displaySize);

        detectionInterval = setInterval(async () => {
            if (isProcessing || !stream) return;

            const detections = await faceapi.detectAllFaces(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.6 }))
                .withFaceLandmarks()
                .withFaceDescriptors();

            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw box outlines
            faceapi.draw.drawDetections(canvas, resizedDetections);

            if (detections.length > 0 && faceMatcher) {
                // Gunakan deteksi pertama
                const bestMatch = faceMatcher.findBestMatch(detections[0].descriptor);
                
                if (bestMatch.label !== 'unknown') {
                    isProcessing = true;
                    $('#scanner-indicator').text('WAJAH COCOK. PROSES KEHADIRAN...');
                    processKioskAttendance(bestMatch.label);
                }
            }
        }, 500);
    }

    function processKioskAttendance(userId) {
        // Find local metadata
        const userObj = enrolledDescriptors.find(item => item.id === userId);
        if (!userObj) {
            isProcessing = false;
            return;
        }

        // POST log presensi
        $.ajax({
            url: "{{ route('biometric-mapping.scan') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                user_id: userId,
                type: kioskMode
            },
            success: (res) => {
                if (res.success) {
                    showResultCard(userObj, res.data);
                    speakText("Presensi berhasil dicatat untuk " + userObj.name);
                } else {
                    toastr.error(res.message);
                    isProcessing = false;
                }
            },
            error: (xhr) => {
                toastr.error(xhr.responseJSON?.message || 'Gagal mengirim scan.');
                isProcessing = false;
                $('#scanner-indicator').text('PEMINDAIAN AKTIF...');
            }
        });
    }

    function showResultCard(user, data) {
        $('#result-idle').hide();
        $('#result-photo').attr('src', user.photo);
        $('#result-name').text(user.name);
        $('#result-type').text(user.type);
        $('#result-time').text(data.time);
        
        let statusText = 'Hadir';
        if (data.status === 'late') {
            statusText = 'Terlambat (' + data.late_minutes + ' Menit)';
            $('#result-status').removeClass('text-success').addClass('text-warning').text(statusText);
        } else {
            $('#result-status').removeClass('text-warning').addClass('text-success').text(statusText);
        }

        $('#result-success').fadeIn();

        let count = 3;
        const countdownInterval = setInterval(() => {
            count--;
            if (count <= 0) {
                clearInterval(countdownInterval);
                resetKioskResult();
            } else {
                $('#result-delay').text('Kiosk akan memindai ulang dalam ' + count + ' detik...');
            }
        }, 1000);
    }

    function resetKioskResult() {
        $('#result-success').hide();
        $('#result-idle').fadeIn();
        isProcessing = false;
        $('#scanner-indicator').text('PEMINDAIAN AKTIF...');
    }

    function speakText(text) {
        if ('speechSynthesis' in window) {
            const speech = new SpeechSynthesisUtterance(text);
            speech.lang = 'id-ID';
            window.speechSynthesis.speak(speech);
        }
    }

    function toggleFullscreen() {
        const elem = document.getElementById('kiosk-container-full');
        if (!document.fullscreenElement) {
            elem.requestFullscreen().catch(err => {
                toastr.error('Error enabling fullscreen mode.');
            });
        } else {
            document.exitFullscreen();
        }
    }
</script>
@endpush
