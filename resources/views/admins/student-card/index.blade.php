<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Santri PPTQ Cahaya Tasbih</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css"
        integrity="sha384-uRkJkWhSCmMNnNkcvXMbTHwbTqoZ1ALoEjkBZm9zGjwDnfzJWDDV478mQzUWLQMY" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&family=Kredit:wght@400;700&display=swap"
        rel="stylesheet">

    <style>
        @page {
            size: landscape;
            margin: 2.54cm;
        }

        body {
            font-family: 'Raleway', Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }

        .card {
            width: 85.60mm;
            height: 53.98mm;
            background-image: url('{{ asset($background) }}');
            background-size: cover;
            background-position: center;
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-header {
            display: flex;
            align-items: center;
            padding: 5px;
        }

        .logo {
            max-width: 100%;
            max-height: 20px;
            margin-right: 10px;
            margin-top: 10px;
        }

        .institution-info {
            color: white;
            font-size: 10px;
            position: absolute;
            top: 5px;
            right: 5px;
            text-align: right;
        }

        .institution-info h3 {
            font-size: 12px;
            margin: 0;
            font-weight: 700;
        }

        .institution-info h3.yellow {
            color: #FFFF00;
        }

        .card-content {
            padding: 5px;
            color: #fff;
            font-size: 9px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .student-info {
            text-align: center;
            margin-bottom: 5px;
            position: relative;
            padding: 10px 0;
        }

        .student-info::before,
        .student-info::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            height: 1px;
            background-image: linear-gradient(to right, transparent, #fff, transparent);
            z-index: 1;
        }

        .student-info::before {
            top: 0;
        }

        .student-info::after {
            bottom: 0;
        }

        .student-name {
            position: relative;
            z-index: 2;
            font-weight: 700;
            font-size: 14px;
        }

        .student-nis {
            position: relative;
            z-index: 2;
            font-family: 'Kredit', monospace;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: 2px;
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
        }

        .student-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 5px;
        }

        .student-class {
            font-size: 10px;
            font-weight: bold;
        }

        .student-school {
            font-size: 10px;
            font-weight: bold;
        }

        .barcode-container {
            background-color: white;
            padding: 5px;
            border-radius: 5px;
            transform: skewX(-10deg);
            text-align: right;
            position: absolute;
            bottom: 5px;
            right: 5px;
        }

        .barcode img {
            width: 60px;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header row">
                        <div class="col-4">
                            <img src="{{ asset('assets/media/logos/logo-full.png') }}" alt="Logo" class="logo">
                        </div>
                        <div class="col-8 institution-info">
                            <h3 class="yellow">Kartu Santri</h3>
                            <h3>PPTQ Cahaya Tasbih</h3>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="student-info">
                            <div class="student-name">
                                {{ $student->name ?? '' }}
                            </div>
                            <div class="student-info-row">
                                <div class="student-nis">
                                    {{ $student->nis ?? '' }}
                                </div>
                                <div class="student-class">
                                    {{ $student->classroom?->name ?? '' }}
                                </div>
                                <div class="student-school">
                                    {{ $student->classroom?->school?->name ?? '' }}
                                </div>
                            </div>
                        </div>
                        <div class="barcode-container">
                            {!! DNS1D::getBarcodeHTML($student->barcode ?? '', 'C128', 1, 20) !!}
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