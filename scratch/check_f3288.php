<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$bt1 = \App\Models\BillType::find('f32889d7-c082-4507-8b5f-3512a4b01685');
$bt2 = \App\Models\BillType::find('f45dc672-1bfc-4f4b-bb1e-0169c7ed06f7');

echo "=== BT1 (f32889d7...) ===\n";
if ($bt1) {
    echo "Name: {$bt1->name} | Type: {$bt1->type} | InputType: {$bt1->payment_input_type} | AY_ID: {$bt1->academic_year_id}\n";
} else {
    echo "Not found\n";
}

echo "\n=== BT2 (f45dc672...) ===\n";
if ($bt2) {
    echo "Name: {$bt2->name} | Type: {$bt2->type} | InputType: {$bt2->payment_input_type} | AY_ID: {$bt2->academic_year_id}\n";
} else {
    echo "Not found\n";
}
