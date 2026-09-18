<?php
$f = 'public/audit_vps.php';
$content = file_get_contents($f);

$oldFunc = <<<'EOD'
function groupDataBySantriAndTagihan($data) {
    $res = [];
    foreach($data as $r) {
        $k = $r->santri . '|||' . $r->kelas;
        if(!isset($res[$k])) {
            $res[$k] = [
                'santri' => $r->santri, 
                'kelas' => $r->kelas, 
                'tagihan_groups' => []
            ];
        }
        $tagihan_name = $r->tagihan;
        if(!isset($res[$k]['tagihan_groups'][$tagihan_name])) {
            $res[$k]['tagihan_groups'][$tagihan_name] = [];
        }
        $res[$k]['tagihan_groups'][$tagihan_name][] = $r;
    }
    return array_values($res);
}
EOD;

$newFunc = <<<'EOD'
function groupDataBySantriAndTagihan($data) {
    $res = [];
    foreach($data as $r) {
        $k = $r->santri . '|||' . $r->kelas;
        if(!isset($res[$k])) {
            $res[$k] = [
                'santri' => $r->santri, 
                'kelas' => $r->kelas, 
                'tagihan_groups' => []
            ];
        }
        $tagihan_name = $r->tagihan;
        if(!isset($res[$k]['tagihan_groups'][$tagihan_name])) {
            $res[$k]['tagihan_groups'][$tagihan_name] = [];
        }
        $res[$k]['tagihan_groups'][$tagihan_name][] = $r;
    }
    
    // Urutkan tiap grup tagihan berdasarkan Tahun lalu Bulan
    foreach($res as &$studentGroup) {
        foreach($studentGroup['tagihan_groups'] as $tName => &$items) {
            usort($items, function($a, $b) {
                $yA = isset($a->year) ? (int)$a->year : 0;
                $yB = isset($b->year) ? (int)$b->year : 0;
                if ($yA !== $yB) return $yA <=> $yB;
                
                $mA = isset($a->month) ? (int)$a->month : 0;
                $mB = isset($b->month) ? (int)$b->month : 0;
                return $mA <=> $mB;
            });
        }
    }
    unset($studentGroup, $items);

    return array_values($res);
}
EOD;

$content = str_replace($oldFunc, $newFunc, $content);
file_put_contents($f, $content);
echo "REPLACED\n";
