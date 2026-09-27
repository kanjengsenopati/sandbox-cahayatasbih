<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

// Replace onAcademicYearChange block
$pattern = '/function onAcademicYearChange\(\) \{.*?fetchMasterDiff.*?;\s*\}/s';

$replacement = <<<EOD
function filterBillTypesDropdown() {
            var yearId = document.getElementById('select-filter-academic-year') ? document.getElementById('select-filter-academic-year').value : '';
            var schoolId = document.getElementById('select-filter-school') ? document.getElementById('select-filter-school').value : '';
            var billSelect = document.getElementById('select-filter-bill-type');
            if(!billSelect) return;
            var options = billSelect.querySelectorAll('option');

            var currentSelectedValid = false;

            options.forEach(function(opt) {
                if (!opt.value) {
                    opt.style.display = ''; // "Semua Jenis Tagihan" always visible
                    if (billSelect.value === opt.value) currentSelectedValid = true;
                    return;
                }
                
                var optYearId = opt.getAttribute('data-academic-year-id');
                var optSchoolId = opt.getAttribute('data-school-id');
                
                var matchYear = !yearId || optYearId === yearId;
                var matchSchool = !schoolId || !optSchoolId || optSchoolId === schoolId;

                if (matchYear && matchSchool) {
                    opt.style.display = '';
                    if (billSelect.value === opt.value) currentSelectedValid = true;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (!currentSelectedValid) {
                billSelect.value = '';
            }
        }

        function onAcademicYearChange() {
            filterBillTypesDropdown();
            fetchMasterDiff(document.getElementById('current-merge-module').value);
        }
EOD;

$content = preg_replace($pattern, $replacement, $content, 1);

// Add sorting to renderCurrentPage
$pattern2 = '/function renderCurrentPage\(\) \{.*?var tbody = document.getElementById/s';
$replacement2 = <<<EOD
window.currentSortDirection = 'asc';
        function sortSyncItemsByStatus() {
            if (!window.currentSyncItems || window.currentSyncItems.length === 0) return;
            window.currentSortDirection = window.currentSortDirection === 'asc' ? 'desc' : 'asc';
            
            window.currentSyncItems.sort(function(a, b) {
                var valA = a.status || '';
                var valB = b.status || '';
                if (valA < valB) return window.currentSortDirection === 'asc' ? -1 : 1;
                if (valA > valB) return window.currentSortDirection === 'asc' ? 1 : -1;
                return 0;
            });
            
            window.currentPage = 1;
            renderCurrentPage();
            
            setTimeout(function() {
                var icon = document.getElementById('sort-icon-status');
                if (icon) {
                    icon.className = window.currentSortDirection === 'asc' ? 'fas fa-sort-up ms-1' : 'fas fa-sort-down ms-1';
                }
            }, 50);
        }

        function renderCurrentPage() {
            var tbody = document.getElementById
EOD;

$content = preg_replace($pattern2, $replacement2, $content, 1);

// Replace table header
$pattern3 = '/<th>Perbandingan Status Tagihan \(Aplikasi Lama &rarr; Lokal\)<\/th>/';
$replacement3 = '<th class="cursor-pointer text-primary" onclick="sortSyncItemsByStatus()" style="cursor: pointer;" title="Klik untuk mengurutkan Identik / Butuh Sync">Perbandingan Status Tagihan (Aplikasi Lama &rarr; Lokal) <i class="fas fa-sort ms-1" id="sort-icon-status"></i></th>';
$content = preg_replace($pattern3, $replacement3, $content, 1);

file_put_contents('resources/views/admins/admin/audit/sync.blade.php', $content);
echo "Regex replacements done!\n";
