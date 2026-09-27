<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

// Fix column 6 header text (remove "Status" and "Aplikasi")
$searchHeader = 'Perbandingan Status Tagihan (Aplikasi Lama &rarr; Lokal)';
$replaceHeader = 'Perbandingan Tagihan (Lama &rarr; Lokal)';
$content = str_replace($searchHeader, $replaceHeader, $content);

// Ensure the form submit triggers state saving safely
$searchStateSave = <<<EOD
            // Listen to form submit to save state
            var mergeForm = document.getElementById('form-confirm-merge');
            if (mergeForm) {
                mergeForm.addEventListener('submit', function() {
EOD;

$replaceStateSave = <<<EOD
            // Listen to form submit to save state
            var mergeForm = document.getElementById('form-confirm-merge');
            var btnMerge = document.getElementById('btn-submit-merge');
            if (btnMerge) {
                btnMerge.addEventListener('click', function() {
EOD;
$content = str_replace(str_replace("\r\n", "\n", $searchStateSave), str_replace("\r\n", "\n", $replaceStateSave), str_replace("\r\n", "\n", $content));

// Update the DOMContentLoaded restoration logic to correctly trigger cascades
$searchDOM = <<<EOD
                if (document.getElementById('select-filter-school')) {
                    document.getElementById('select-filter-school').value = state.school || '';
                }
                if (document.getElementById('select-filter-classroom')) {
                    document.getElementById('select-filter-classroom').value = state.classroom || '';
                }
                if (document.getElementById('select-filter-academic-year')) {
                    document.getElementById('select-filter-academic-year').value = state.academic_year || '';
                }
                
                if (typeof filterBillTypesDropdown === 'function') {
                    filterBillTypesDropdown();
                }
                
                if (document.getElementById('select-filter-bill-type')) {
                    document.getElementById('select-filter-bill-type').value = state.bill_type || '';
                }
EOD;

$replaceDOM = <<<EOD
                if (document.getElementById('select-filter-school')) {
                    document.getElementById('select-filter-school').value = state.school || '';
                    if (typeof onSchoolFilterChange === 'function') onSchoolFilterChange();
                }
                if (document.getElementById('select-filter-classroom')) {
                    document.getElementById('select-filter-classroom').value = state.classroom || '';
                }
                if (document.getElementById('select-filter-academic-year')) {
                    document.getElementById('select-filter-academic-year').value = state.academic_year || '';
                    if (typeof onAcademicYearChange === 'function') onAcademicYearChange();
                }
                
                if (typeof filterBillTypesDropdown === 'function') {
                    filterBillTypesDropdown();
                }
                
                if (document.getElementById('select-filter-bill-type')) {
                    document.getElementById('select-filter-bill-type').value = state.bill_type || '';
                }
EOD;

$content = str_replace(str_replace("\r\n", "\n", $searchDOM), str_replace("\r\n", "\n", $replaceDOM), str_replace("\r\n", "\n", $content));

file_put_contents('resources/views/admins/admin/audit/sync.blade.php', $content);
echo "Patched sync.blade.php successfully!\n";
