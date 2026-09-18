<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

// 1. ADD SEARCH INPUT TO AKTIF
$aktifTop = <<<'EOD'
<div id="content-aktif" class="max-w-6xl mx-auto px-5 space-y-6">
EOD;
$aktifTopNew = <<<'EOD'
<div id="content-aktif" class="max-w-6xl mx-auto px-5 space-y-6">
    <div class="relative mb-2">
        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <input type="text" id="search-aktif" onkeyup="filterStudents('aktif')" placeholder="Cari nama siswa atau kelas..." class="bg-white border border-slate-200 text-slate-800 text-sm font-medium rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-12 p-3.5 shadow-[0_2px_10px_rgb(0,0,0,0.02)] placeholder-slate-400">
    </div>
EOD;
$c = str_replace($aktifTop, $aktifTopNew, $c);


// 2. ADD SEARCH INPUT TO RIWAYAT
$riwayatTop = <<<'EOD'
<div id="content-riwayat" class="max-w-6xl mx-auto px-5 hidden">
    <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h2 class="text-h2 text-emerald-600 flex items-center gap-2">
                <span class="bg-emerald-100 p-1.5 rounded-full"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                Riwayat Perbaikan Valid
            </h2>
        </div>
        <div>
EOD;
$riwayatTopNew = <<<'EOD'
<div id="content-riwayat" class="max-w-6xl mx-auto px-5 hidden space-y-6">
    <div class="relative mb-2">
        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <input type="text" id="search-riwayat" onkeyup="filterStudents('riwayat')" placeholder="Cari nama riwayat perbaikan siswa..." class="bg-white border border-slate-200 text-slate-800 text-sm font-medium rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-12 p-3.5 shadow-[0_2px_10px_rgb(0,0,0,0.02)] placeholder-slate-400">
    </div>
    <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h2 class="text-h2 text-emerald-600 flex items-center gap-2">
                <span class="bg-emerald-100 p-1.5 rounded-full"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                Riwayat Perbaikan Valid
            </h2>
        </div>
        <div>
EOD;
$c = str_replace($riwayatTop, $riwayatTopNew, $c);


// 3. ADD WRAPPER CLASSES AND DATA ATTRIBUTES TO STUDENT LOOPS
$studentLoop2 = <<<'EOD'
                    <?php $anomaly_category = "cat2"; $no = 1; foreach($g2 as $g):?>
                    <div class="border-b border-slate-100 p-5 hover:bg-slate-50/50">
EOD;
$studentLoop2New = <<<'EOD'
                    <?php $anomaly_category = "cat2"; $no = 1; foreach($g2 as $g):?>
                    <div class="student-wrapper-aktif border-b border-slate-100 p-5 hover:bg-slate-50/50" data-search="<?= strtolower(htmlspecialchars($g['santri'].' '.$g['kelas'])) ?>">
EOD;
$c = str_replace($studentLoop2, $studentLoop2New, $c);

$studentLoop3 = <<<'EOD'
                    <?php $anomaly_category = "cat3"; $no = 1; foreach($g3 as $g):?>
                    <div class="border-b border-slate-100 p-5 hover:bg-slate-50/50">
EOD;
$studentLoop3New = <<<'EOD'
                    <?php $anomaly_category = "cat3"; $no = 1; foreach($g3 as $g):?>
                    <div class="student-wrapper-aktif border-b border-slate-100 p-5 hover:bg-slate-50/50" data-search="<?= strtolower(htmlspecialchars($g['santri'].' '.$g['kelas'])) ?>">
EOD;
$c = str_replace($studentLoop3, $studentLoop3New, $c);

$studentLoop5 = <<<'EOD'
                    <?php $anomaly_category = "cat5"; $no = 1; foreach($g6 as $g):?>
                    <div class="border-b border-slate-100 p-5 hover:bg-slate-50/50">
EOD;
$studentLoop5New = <<<'EOD'
                    <?php $anomaly_category = "cat5"; $no = 1; foreach($g6 as $g):?>
                    <div class="student-wrapper-aktif border-b border-slate-100 p-5 hover:bg-slate-50/50" data-search="<?= strtolower(htmlspecialchars($g['santri'].' '.$g['kelas'])) ?>">
EOD;
$c = str_replace($studentLoop5, $studentLoop5New, $c);

$historyLoop = <<<'EOD'
                    <?php foreach($history_repairs as $r): ?>
                    <div class="border-b border-slate-100 p-5 hover:bg-slate-50/50 flex justify-between items-center">
EOD;
$historyLoopNew = <<<'EOD'
                    <?php foreach($history_repairs as $r): ?>
                    <div class="student-wrapper-riwayat border-b border-slate-100 p-5 hover:bg-slate-50/50 flex justify-between items-center" data-search="<?= strtolower(htmlspecialchars($r->santri.' '.$r->kelas.' '.$r->tagihan)) ?>">
EOD;
$c = str_replace($historyLoop, $historyLoopNew, $c);


// 4. ADD JS LOGIC
$jsOld = <<<'EOD'
    function switchTab(tabId) {
        if (tabId === 'riwayat') {
EOD;
$jsNew = <<<'EOD'
    function filterStudents(tab) {
        let input = document.getElementById('search-' + tab).value.toLowerCase();
        sessionStorage.setItem('search_' + tab, input);
        
        let wrappers = document.querySelectorAll('.student-wrapper-' + tab);
        wrappers.forEach(w => {
            let text = w.getAttribute('data-search');
            if (text.includes(input)) {
                w.style.display = '';
            } else {
                w.style.display = 'none';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        let savedTab = sessionStorage.getItem('active_tab');
        if(savedTab) {
            switchTab(savedTab);
        }
        
        let searchAktif = sessionStorage.getItem('search_aktif');
        if(searchAktif) {
            let el = document.getElementById('search-aktif');
            if(el) { el.value = searchAktif; filterStudents('aktif'); }
        }
        
        let searchRiwayat = sessionStorage.getItem('search_riwayat');
        if(searchRiwayat) {
            let el = document.getElementById('search-riwayat');
            if(el) { el.value = searchRiwayat; filterStudents('riwayat'); }
        }
    });

    function switchTab(tabId) {
        sessionStorage.setItem('active_tab', tabId);
        if (tabId === 'riwayat') {
EOD;
$c = str_replace($jsOld, $jsNew, $c);

file_put_contents($f, $c);
echo "PATCH SUCCESSFUL\n";
