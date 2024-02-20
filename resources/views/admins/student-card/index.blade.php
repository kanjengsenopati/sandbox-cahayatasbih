<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Kartu Siswa PDF</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css"
        integrity="sha384-uRkJkWhSCmMNnNkcvXMbTHwbTqoZ1ALoEjkBZm9zGjwDnfzJWDDV478mQzUWLQMY" crossorigin="anonymous">

    <style>
        @page {
            size: landscape;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }

        .card {
            width: 50%;
            height: 50%;
            background-image: url('{{ asset($background) }}');
            background-size: cover;
            background-position: center;
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        .logo {
            width: 25%;
            height: auto;
            margin-right: 20px;
        }

        .institution-info {
            color: white;
            text-align: center;
        }

        .institution-info h3,
        .institution-info p {
            margin: 0;
        }

        .institution-info h3 {
            font-size: 18px;
        }

        .institution-info p {
            font-size: 14px;
        }

        .card-content {
            padding: 20px;
            color: #fff;
        }

        .card-content h2 {
            margin: 0;
            text-align: center;
        }

        .student-info p {
            color: #fff;
        }

        .barcode {
            max-width: calc(100% - 20px);
            /* Mengikuti lebar card dengan tambahan margin */
            height: auto;
            margin-top: 20px;
            margin-left: 10px;
            /* Tambahkan margin di samping barcode */
        }

        .student-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
            float: left;
            /* Tambahkan properti float */
        }

        .student-info {
            overflow: hidden;
            /* Gunakan overflow: hidden untuk mencegah nama dan info melewati foto */
        }

        .student-info p {
            margin: 0;
            /* Hapus margin bawaan */
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-8">
                <div class="card">
                    <div class="card-header row">
                        <div class="col-4 text-center">
                            <img src="{{ asset('assets/media/logos/logo-full.png') }}" alt="Logo" class="logo">
                        </div>
                        <div class="col-8 institution-info text-center">
                            <h3>Pondok Pesantren Cahaya Tasbih</h3>
                            <p>Jl. Raya Demak – Kudus Km.14 Desa Sari - Kec Gajah Demak</p>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="row">
                            <div class="col-6">
                                <img src="{{ url('https://ui-avatars.com/api/?name=' . $student->name . '&color=7F9CF5&background=EBF4FF') }}"
                                    alt="Foto Siswa" class="student-photo">
                            </div>
                            <div class="col-6 student-info">
                                <!-- Tambahkan class student-info di sini -->
                                <h3>Nama: {{ $student->name }}</h3>
                                <h3>NIS: {{ $student->nisn }}</h3>
                                <h3>Kelas: {{ $student->classroom->name }}</h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="justify-content-center">
                                    <div class="mb-3 barcode">{!! DNS1D::getBarcodeHTML($student->nisn, 'C128', 1, 33)
                                        !!}</div>
                                    {{-- <img
                                        src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(75)->errorCorrection('H')->generate($student->nisn)) !!} "
                                        alt="Barcode" class="barcode"> --}}
                                    {{-- <img
                                        src="https://barcode.tec-it.com/barcode.ashx?data={{ $student->barcode }}&code=Code128&dpi=96"
                                        alt="Barcode" class="barcode"> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-OERHl4yP9FB6zXMsHNHtCC1EPlF7RDx/J5z4iyycdNQGOz8P9yW5zNHfRuy0GgWFN" crossorigin="anonymous">
</script>

</html>