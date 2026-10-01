<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

$search1 = <<<EOD
            thead.innerHTML = '<tr class="text-start text-gray-500 fw-bolder text-uppercase tracking-wider">' +
                '<th class="w-40px px-3"><input class="form-check-input" type="checkbox" id="chk-all-diff" onchange="toggleAllDiffCheckboxes(this)"></th>' +
                '<th class="w-50px">No.</th>' +
                '<th>ID / Code</th>' +
                '<th>Nama Record</th>' +
                '<th>Perbandingan Status Tagihan (Aplikasi Lama &rarr; Lokal)</th>' +
                '</tr>';
EOD;

$replace1 = <<<EOD
            thead.innerHTML = '<tr class="text-start text-gray-500 fw-bolder text-uppercase tracking-wider">' +
                '<th class="w-40px px-3"><input class="form-check-input" type="checkbox" id="chk-all-diff" onchange="toggleAllDiffCheckboxes(this)"></th>' +
                '<th class="w-50px">No.</th>' +
                '<th>ID / Code</th>' +
                '<th>Nama Record</th>' +
                '<th class="cursor-pointer text-primary" onclick="sortSyncItemsByStatus()" style="cursor: pointer;" title="Klik untuk mengurutkan Identik / Butuh Sync">Perbandingan Status Tagihan (Aplikasi Lama &rarr; Lokal) <i class="fas fa-sort ms-1" id="sort-icon-status"></i></th>' +
                '</tr>';
EOD;

$content = str_replace($search1, $replace1, $content);

$search2 = <<<EOD
    function renderCurrentPage() {
EOD;

$replace2 = <<<EOD
    window.currentSortDirection = 'asc';
    function sortSyncItemsByStatus() {
        if (!window.currentSyncItems || window.currentSyncItems.length === 0) return;
        window.currentSortDirection = window.currentSortDirection === 'asc' ? 'desc' : 'asc';
        
        window.currentSyncItems.sort(function(a, b) {
            var valA = a.status || '';
            var valB = b.status || '';
            // Custom sort logic to group EXACT_MATCH together and UPDATE_REQUIRED together
            if (valA < valB) return window.currentSortDirection === 'asc' ? -1 : 1;
            if (valA > valB) return window.currentSortDirection === 'asc' ? 1 : -1;
            return 0;
        });
        
        window.currentPage = 1;
        renderCurrentPage();
        
        // Update sort icon
        setTimeout(function() {
            var icon = document.getElementById('sort-icon-status');
            if (icon) {
                icon.className = window.currentSortDirection === 'asc' ? 'fas fa-sort-up ms-1' : 'fas fa-sort-down ms-1';
            }
        }, 50);
    }

    function renderCurrentPage() {
EOD;

$content = str_replace($search2, $replace2, $content);
file_put_contents('resources/views/admins/admin/audit/sync.blade.php', $content);
echo "Added sort functionality\n";
