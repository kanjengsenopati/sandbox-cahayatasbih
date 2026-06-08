<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;

$unspecified = Student::where(function($q) {
    $q->whereNull('gender')->orWhere('gender', '');
})->get();

$maleKeywords = [
    'MUHAMMAD', 'MOHAMMAD', 'MOH', 'AHMAD', 'ACHMAD', 'BAGUS', 'FADIL', 'SATRIA', 
    'BAYU', 'RAFI', 'FAHRI', 'ALIF', 'IKHSAN', 'ADITYA', 'YUSUF', 'IBRAHIM', 
    'FAJAR', 'REZA', 'HADI', 'EKO', 'AGUS', 'FEBRI', 'SAMSUL', 'BAHRI', 'PUTRA', 
    'FADHLURRAHMAN', 'BINTANG', 'RIZKY', 'ILHAM', 'ARIF', 'HIDAYAT', 'DWI', 'AKBAR',
    'RAMADHAN', 'WIRYA', 'BUDI', 'NUGROHO', 'SULTON', 'WAHAB', 'HASYIM', 'MUSTOFA',
    'HAMDAN', 'ALI', 'MUCHTAR', 'KARIM', 'SUBANDI', 'BROMO', 'ALAM', 'ALKHOLIDH',
    'PRAYOGO', 'SAPUTRA', 'LUDFI', 'KHOIRIL', 'EKSA', 'CHALEO', 'ARGAPRADITHA',
    'KHAFIS', 'ABY', 'ANANDIKA', 'DEVIN', 'FANSYURI', 'RAKHMAN', 'ABDILLAH', 'SYAHRIAL',
    'MAULANA', 'AZIZ', 'HABIB', 'FAUZAN', 'GALIH', 'DIMAS', 'FATHUR', 'WICAKSONO'
];

$femaleKeywords = [
    'SITI', 'DEWI', 'PUTRI', 'SALSABILA', 'ANISA', 'ANISAH', 'KHOIRUNNISA', 
    'NADA', 'ZAHRA', 'BELLA', 'SARI', 'ZAKIA', 'ALIFIA', 'MARSYALIMA', 'ZAHWA', 
    'RARAS', 'WENINGTYASTUTI', 'HAJAR', 'KARTIKA', 'MUZAENAH', 'ATIK', 'SORAYA', 
    'ANJANI', 'JANNAH', 'AAISYAH', 'AADILLAH', 'MARSYA', 'AULIA', 'NUR', 'AINUN',
    'NI\'MAH', 'NI\'MATUL', 'LATIFAH', 'FITRI', 'SABRINA', 'NILA', 'ZAHIRA', 'MUTIARA',
    'TSALTSABILA', 'FATIHAH', 'AIDA', 'NADIA', 'AMALIA', 'MUTI'
];

$inferredMale = 0;
$inferredFemale = 0;
$ambiguous = [];

foreach ($unspecified as $s) {
    $name = strtoupper($s->name);
    $words = preg_split('/[\s,.]+/', $name);
    
    $isMale = false;
    $isFemale = false;
    
    // Check for explicit female indicators
    foreach ($femaleKeywords as $fk) {
        if (in_array($fk, $words)) {
            $isFemale = true;
            break;
        }
    }
    
    // Check for explicit male indicators
    foreach ($maleKeywords as $mk) {
        if (in_array($mk, $words)) {
            $isMale = true;
            break;
        }
    }
    
    // Resolve conflict or assign
    if ($isMale && !$isFemale) {
        $inferredMale++;
    } elseif ($isFemale && !$isMale) {
        $inferredFemale++;
    } else {
        $ambiguous[] = [
            'id' => $s->id,
            'name' => $s->name,
            'isMale' => $isMale,
            'isFemale' => $isFemale,
            'classroom' => $s->classroom->name ?? 'None'
        ];
    }
}

echo "Total Unspecified: " . $unspecified->count() . "\n";
echo "Inferred Male: $inferredMale\n";
echo "Inferred Female: $inferredFemale\n";
echo "Ambiguous/Unresolved: " . count($ambiguous) . "\n\n";

if (count($ambiguous) > 0) {
    echo "=== AMBIGUOUS SAMPLES ===\n";
    foreach (array_slice($ambiguous, 0, 30) as $a) {
        echo "Name: {$a['name']} | Class: {$a['classroom']} | MaleHint: " . ($a['isMale']?'YES':'NO') . " | FemaleHint: " . ($a['isFemale']?'YES':'NO') . "\n";
    }
}
