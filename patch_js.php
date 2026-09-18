<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

$jsOld = <<<'EOD'
<script>
function switchTab(tab) {
EOD;

$jsNew = <<<'EOD'
<script>
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

function switchTab(tab) {
    sessionStorage.setItem('active_tab', tab);
EOD;

$c = str_replace($jsOld, $jsNew, $c);
file_put_contents($f, $c);
echo "JS INJECTED\n";
