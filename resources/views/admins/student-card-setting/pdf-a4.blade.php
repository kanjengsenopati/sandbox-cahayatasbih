<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cetak Kartu Santri A4 Grid</title>
    <style>
        @page {
            margin: 10mm 12mm;
            size: A4 portrait;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Raleway', Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
        }
        .grid-td {
            padding: 2.5mm;
            vertical-align: top;
            width: 50%;
        }
        .card-container {
            width: 85.6mm;
            height: 53.98mm;
            position: relative;
            overflow: hidden;
            border: 0.1mm dashed #999999;
            box-sizing: border-box;
            background-color: #ffffff;
        }
        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 85.6mm;
            height: 53.98mm;
            z-index: 1;
        }
        .element {
            position: absolute;
            z-index: 2;
        }
        .photo-box {
            background-color: #cccccc;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border: 1px solid #ffffff;
        }
        .code-box {
            background-color: #ffffff;
            padding: 2px;
            text-align: center;
        }
        .code-box img {
            max-width: 100%;
            max-height: 100%;
            display: block;
            margin: 0 auto;
        }
        .font-kredit {
            font-family: 'Kredit', 'Courier New', Courier, monospace;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @php
        $totalCards = count($studentsData);
        $cardsPerPage = $cols * $rows;
    @endphp

    @foreach($studentsData->chunk($cardsPerPage) as $pageIndex => $pageChunk)
        <table class="grid-table">
            @for($r = 0; $r < $rows; $r++)
                <tr>
                    @for($c = 0; $c < $cols; $c++)
                        @php
                            $index = ($r * $cols) + $c;
                            $data = $pageChunk->values()->get($index);
                        @endphp
                        <td class="grid-td">
                            @if($data)
                                @php
                                    $student = $data['student'];
                                    $codeHtml = $data['code_html'];
                                @endphp
                                <div class="card-container">
                                    {{-- Background Image --}}
                                    @if($background)
                                        <img src="{{ $background }}" class="background-image" />
                                    @else
                                        <div class="background-image" style="background: linear-gradient(135deg, #1a4731, #10b981);"></div>
                                    @endif

                                    {{-- Logo --}}
                                    @if($layout['logo']['show'] ?? true)
                                        <img class="element" src="{{ public_path('assets/media/logos/logo-full.png') }}" style="
                                            top: {{ $layout['logo']['top'] ?? 5 }}mm;
                                            left: {{ $layout['logo']['left'] ?? 5 }}mm;
                                            width: {{ $layout['logo']['width'] ?? 25 }}mm;
                                            height: {{ $layout['logo']['height'] ?? 8 }}mm;
                                            object-fit: contain;
                                        " />
                                    @endif

                                    {{-- Title --}}
                                    @if($layout['title']['show'] ?? true)
                                        <div class="element" style="
                                            top: {{ $layout['title']['top'] ?? 5 }}mm;
                                            left: {{ $layout['title']['left'] ?? 45 }}mm;
                                            width: {{ 86 - ($layout['title']['left'] ?? 45) }}mm;
                                            color: {{ $layout['title']['color'] ?? '#FFFF00' }};
                                            font-size: {{ $layout['title']['font_size'] ?? 12 }}pt;
                                            font-weight: {{ $layout['title']['font_weight'] ?? 'bold' }};
                                            text-align: {{ $layout['title']['text_align'] ?? 'right' }};
                                            white-space: nowrap;
                                            overflow: hidden;
                                        ">
                                            {{ $layout['title']['text'] ?? 'Kartu Santri' }}
                                        </div>
                                    @endif

                                    {{-- Subtitle --}}
                                    @if($layout['subtitle']['show'] ?? true)
                                        <div class="element" style="
                                            top: {{ $layout['subtitle']['top'] ?? 10 }}mm;
                                            left: {{ $layout['subtitle']['left'] ?? 45 }}mm;
                                            width: {{ 86 - ($layout['subtitle']['left'] ?? 45) }}mm;
                                            color: {{ $layout['subtitle']['color'] ?? '#FFFFFF' }};
                                            font-size: {{ $layout['subtitle']['font_size'] ?? 10 }}pt;
                                            font-weight: {{ $layout['subtitle']['font_weight'] ?? 'bold' }};
                                            text-align: {{ $layout['subtitle']['text_align'] ?? 'right' }};
                                            white-space: nowrap;
                                            overflow: hidden;
                                        ">
                                            {{ $layout['subtitle']['text'] ?? 'PPTQ Cahaya Tasbih' }}
                                        </div>
                                    @endif

                                    {{-- Photo --}}
                                    @if($layout['photo']['show'] ?? false)
                                        <div class="element photo-box" style="
                                            top: {{ $layout['photo']['top'] ?? 18 }}mm;
                                            left: {{ $layout['photo']['left'] ?? 5 }}mm;
                                            width: {{ $layout['photo']['width'] ?? 18 }}mm;
                                            height: {{ $layout['photo']['height'] ?? 24 }}mm;
                                            border-radius: {{ $layout['photo']['border_radius'] ?? 2 }}mm;
                                            @if($student->avatar && file_exists(public_path($student->avatar)))
                                                background-image: url('{{ public_path($student->avatar) }}');
                                            @else
                                                background-image: url('{{ public_path('assets/media/avatars/blank.png') }}');
                                            @endif
                                        "></div>
                                    @endif

                                    {{-- Name --}}
                                    @if($layout['name']['show'] ?? true)
                                        <div class="element" style="
                                            top: {{ $layout['name']['top'] ?? 20 }}mm;
                                            left: {{ $layout['name']['left'] ?? 25 }}mm;
                                            color: {{ $layout['name']['color'] ?? '#FFFFFF' }};
                                            font-size: {{ $layout['name']['font_size'] ?? 12 }}pt;
                                            font-weight: {{ $layout['name']['font_weight'] ?? 'bold' }};
                                            white-space: nowrap;
                                        ">
                                            {{ $student->name }}
                                        </div>
                                    @endif

                                    {{-- NIS --}}
                                    @if($layout['nis']['show'] ?? true)
                                        <div class="element {{ ($layout['nis']['font_family'] ?? 'Kredit') === 'Kredit' ? 'font-kredit' : '' }}" style="
                                            top: {{ $layout['nis']['top'] ?? 27 }}mm;
                                            left: {{ $layout['nis']['left'] ?? 25 }}mm;
                                            color: {{ $layout['nis']['color'] ?? '#FFFFFF' }};
                                            font-size: {{ $layout['nis']['font_size'] ?? 14 }}pt;
                                            font-weight: {{ $layout['nis']['font_weight'] ?? 'bold' }};
                                            white-space: nowrap;
                                        ">
                                            {{ $student->nis ?? '-' }}
                                        </div>
                                    @endif

                                    {{-- Classroom --}}
                                    @if($layout['classroom']['show'] ?? true)
                                        <div class="element" style="
                                            top: {{ $layout['classroom']['top'] ?? 35 }}mm;
                                            left: {{ $layout['classroom']['left'] ?? 25 }}mm;
                                            color: {{ $layout['classroom']['color'] ?? '#FFFFFF' }};
                                            font-size: {{ $layout['classroom']['font_size'] ?? 9 }}pt;
                                            font-weight: {{ $layout['classroom']['font_weight'] ?? 'bold' }};
                                            white-space: nowrap;
                                        ">
                                            Kelas: {{ $student->classroom?->name ?? '-' }}
                                        </div>
                                    @endif

                                    {{-- School --}}
                                    @if($layout['school']['show'] ?? true)
                                        <div class="element" style="
                                            top: {{ $layout['school']['top'] ?? 40 }}mm;
                                            left: {{ $layout['school']['left'] ?? 25 }}mm;
                                            color: {{ $layout['school']['color'] ?? '#FFFFFF' }};
                                            font-size: {{ $layout['school']['font_size'] ?? 9 }}pt;
                                            font-weight: {{ $layout['school']['font_weight'] ?? 'bold' }};
                                            white-space: nowrap;
                                        ">
                                            {{ $student->classroom?->school?->name ?? '-' }}
                                        </div>
                                    @endif

                                    {{-- Barcode / QR Code --}}
                                    @if(($layout['code']['show'] ?? true) && $student->barcode)
                                        <div class="element code-box" style="
                                            top: {{ $layout['code']['top'] ?? 42 }}mm;
                                            left: {{ $layout['code']['left'] ?? 55 }}mm;
                                            width: {{ $layout['code']['width'] ?? 26 }}mm;
                                            height: {{ $layout['code']['height'] ?? 8 }}mm;
                                        ">
                                            {!! $codeHtml !!}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div style="width: 85.6mm; height: 53.98mm;"></div>
                            @endif
                        </td>
                    @endfor
                </tr>
            @endfor
        </table>
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
