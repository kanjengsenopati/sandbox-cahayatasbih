<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

// 1. Add sortSyncItemsBySyncStatus
$jsSort = <<<EOD
    window.recentlySyncedIds = @json(session('synced_ids', []));

    window.currentSyncStatusDirection = 'asc';
    function sortSyncItemsBySyncStatus() {
        if (!window.currentSyncItems || window.currentSyncItems.length === 0) return;
        window.currentSyncStatusDirection = window.currentSyncStatusDirection === 'asc' ? 'desc' : 'asc';
        
        window.currentSyncItems.sort(function(a, b) {
            var aSynced = (typeof window.recentlySyncedIds !== 'undefined') && window.recentlySyncedIds.includes(a.id) ? 1 : 0;
            var bSynced = (typeof window.recentlySyncedIds !== 'undefined') && window.recentlySyncedIds.includes(b.id) ? 1 : 0;
            
            if (aSynced !== bSynced) {
                return window.currentSyncStatusDirection === 'asc' ? (bSynced - aSynced) : (aSynced - bSynced);
            }
            
            var valA = a.status || '';
            var valB = b.status || '';
            if (valA < valB) return window.currentSyncStatusDirection === 'asc' ? -1 : 1;
            if (valA > valB) return window.currentSyncStatusDirection === 'asc' ? 1 : -1;
            return 0;
        });
        
        window.currentPage = 1;
        renderCurrentPage();
        
        setTimeout(function() {
            var icon = document.getElementById('sort-icon-sync-status');
            if (icon) {
                icon.className = window.currentSyncStatusDirection === 'asc' ? 'fas fa-sort-up ms-1' : 'fas fa-sort-down ms-1';
            }
        }, 50);
    }
EOD;

$content = str_replace("window.recentlySyncedIds = @json(session('synced_ids', []));", $jsSort, $content);

// 2. Change thead HTML
$searchThead = <<<EOD
'<th class="text-center">Status</th>' +
EOD;

$replaceThead = <<<EOD
'<th class="cursor-pointer text-primary text-center" onclick="sortSyncItemsBySyncStatus()" style="cursor: pointer;" title="Klik untuk mengurutkan berdasarkan Status Sinkronisasi">Status <i class="fas fa-sort ms-1" id="sort-icon-sync-status"></i></th>' +
EOD;

$content = str_replace($searchThead, $replaceThead, $content);

// 3. Update DOMContentLoaded for state restoration
$searchDOM = <<<EOD
        document.addEventListener('DOMContentLoaded', function () {
            // Auto fetch students diff on Tab 1 initial load
            if (typeof fetchMasterDiff === 'function') {
                fetchMasterDiff('students');
            }
EOD;

$replaceDOM = <<<EOD
        document.addEventListener('DOMContentLoaded', function () {
            // Restore state if available
            var savedState = sessionStorage.getItem('syncUIState');
            if (savedState) {
                var state = JSON.parse(savedState);
                
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
                
                sessionStorage.removeItem('syncUIState');
                
                if (typeof fetchMasterDiff === 'function') {
                    var btns = document.querySelectorAll('.btn-mod-tab');
                    var activeBtn = null;
                    btns.forEach(function(b) {
                        if (b.getAttribute('onclick') && b.getAttribute('onclick').includes(state.module)) {
                            activeBtn = b;
                        }
                    });
                    fetchMasterDiff(state.module, activeBtn);
                }
            } else {
                // Auto fetch students diff on Tab 1 initial load
                if (typeof fetchMasterDiff === 'function') {
                    fetchMasterDiff('students');
                }
            }
            
            // Listen to form submit to save state
            var mergeForm = document.getElementById('form-confirm-merge');
            if (mergeForm) {
                mergeForm.addEventListener('submit', function() {
                    var state = {
                        module: document.getElementById('current-merge-module') ? document.getElementById('current-merge-module').value : 'students',
                        school: document.getElementById('select-filter-school') ? document.getElementById('select-filter-school').value : '',
                        classroom: document.getElementById('select-filter-classroom') ? document.getElementById('select-filter-classroom').value : '',
                        academic_year: document.getElementById('select-filter-academic-year') ? document.getElementById('select-filter-academic-year').value : '',
                        bill_type: document.getElementById('select-filter-bill-type') ? document.getElementById('select-filter-bill-type').value : ''
                    };
                    sessionStorage.setItem('syncUIState', JSON.stringify(state));
                });
            }
EOD;

$content = str_replace(str_replace("\r\n", "\n", $searchDOM), $replaceDOM, str_replace("\r\n", "\n", $content));

// 4. Update fetchMasterDiff axios .then()
$searchAxios = <<<EOD
        window.currentSyncItems = data.items || [];
        renderCurrentPage();
EOD;

$replaceAxios = <<<EOD
        window.currentSyncItems = data.items || [];
        
        if (module === 'billing_status' && window.recentlySyncedIds && window.recentlySyncedIds.length > 0) {
            window.currentSyncStatusDirection = 'desc'; 
            sortSyncItemsBySyncStatus();
        } else {
            renderCurrentPage();
        }
EOD;

$content = str_replace(str_replace("\r\n", "\n", $searchAxios), $replaceAxios, str_replace("\r\n", "\n", $content));

file_put_contents('resources/views/admins/admin/audit/sync.blade.php', $content);
echo "Patched sync.blade.php for sorting and state preservation!\n";
