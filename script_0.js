

        window.recentlySyncedIds = [];

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

    window.recentlySyncedIds = [];

        document.addEventListener('DOMContentLoaded', function () {
            // Restore state if available
            var savedState = sessionStorage.getItem('syncUIState');
            if (savedState) {
                var state = JSON.parse(savedState);
                
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
            var btnMerge = document.getElementById('btn-submit-merge');
            if (btnMerge) {
                btnMerge.addEventListener('click', function() {
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

            // Loading state saat form sync disubmit
            var form = document.getElementById('sync-db-form');
            if (form) {
                form.addEventListener('submit', function () {
                    var btn = document.getElementById('btn-sync-submit');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sinkronisasi Berjalan...';
                    }
                });
            }

            // Check All Table logic
            var checkAll = document.getElementById('sync-check-all-tables');
            var tableCheckboxes = document.querySelectorAll('.table-checkbox');
            var groupCheckboxes = document.querySelectorAll('.group-checkbox');

            if (checkAll) {
                checkAll.addEventListener('change', function () {
                    var isChecked = this.checked;
                    tableCheckboxes.forEach(function (cb) {
                        cb.checked = isChecked;
                    });
                    groupCheckboxes.forEach(function (cb) {
                        cb.checked = isChecked;
                    });
                });
            }

            // Group Checkbox logic
            groupCheckboxes.forEach(function (gCb) {
                gCb.addEventListener('change', function () {
                    var group = this.getAttribute('data-group');
                    var isChecked = this.checked;
                    document.querySelectorAll('.table-checkbox[data-group="' + group + '"]').forEach(function (tCb) {
                        tCb.checked = isChecked;
                    });
                    updateMasterCheckbox();
                });
            });

            // Table Checkbox logic
            tableCheckboxes.forEach(function (tCb) {
                tCb.addEventListener('change', function () {
                    var group = this.getAttribute('data-group');
                    var groupCb = document.querySelector('.group-checkbox[data-group="' + group + '"]');
                    if (groupCb) {
                        var siblings = document.querySelectorAll('.table-checkbox[data-group="' + group + '"]');
                        var allSiblingsChecked = Array.from(siblings).every(function (cb) {
                            return cb.checked;
                        });
                        var anySiblingChecked = Array.from(siblings).some(function (cb) {
                            return cb.checked;
                        });
                        groupCb.checked = allSiblingsChecked;
                        groupCb.indeterminate = anySiblingChecked && !allSiblingsChecked;
                    }
                    updateMasterCheckbox();
                });
            });

            function updateMasterCheckbox() {
                if (checkAll) {
                    var allChecked = Array.from(tableCheckboxes).every(function (cb) {
                        return cb.checked;
                    });
                    var anyChecked = Array.from(tableCheckboxes).some(function (cb) {
                        return cb.checked;
                    });
                    checkAll.checked = allChecked;
                    checkAll.indeterminate = anyChecked && !allChecked;
                }
            }
        });

        function toggleMasterPreview() {
            var card = document.getElementById('preview-master-card');
            if (card.style.display === 'none' || card.style.display === '') {
                card.style.display = 'block';
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
                fetchMasterDiff('students');
            } else {
                card.style.display = 'none';
            }
        }

        var currentActiveFilter = 'ALL';

        function filterDiffTable(status, badgeEl) {
            if (badgeEl && currentActiveFilter === status) {
                status = 'ALL';
                badgeEl = null;
            }
            currentActiveFilter = status;

            document.querySelectorAll('.filter-badge').forEach(function(el) {
                el.classList.remove('border', 'border-2', 'border-dark', 'shadow-sm');
                el.style.opacity = (status === 'ALL') ? '1' : '0.4';
            });

            var resetBtn = document.getElementById('btn-reset-diff-filter');
            var filterLabel = document.getElementById('txt-active-filter-label');

            if (badgeEl && status !== 'ALL') {
                badgeEl.style.opacity = '1';
                badgeEl.classList.add('border', 'border-2', 'border-dark', 'shadow-sm');
                if (resetBtn) resetBtn.classList.remove('d-none');
                if (filterLabel) filterLabel.innerText = badgeEl.innerText.split(':')[0].replace(/^[^\s]+\s*/, '');
            } else {
                if (resetBtn) resetBtn.classList.add('d-none');
            }

            var rows = document.querySelectorAll('#tbody-diff-preview tr[data-status]');
            var visibleCount = 0;

            rows.forEach(function(row) {
                var rowStatus = row.getAttribute('data-status');
                if (status === 'ALL' || rowStatus === status) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            var emptyMsg = document.getElementById('tr-filter-empty-msg');
            if (visibleCount === 0 && rows.length > 0) {
                if (!emptyMsg) {
                    var tbody = document.getElementById('tbody-diff-preview');
                    tbody.insertAdjacentHTML('beforeend', '<tr id="tr-filter-empty-msg"><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-filter me-2"></i> Tidak ada data dengan status filter ini.</td></tr>');
                }
            } else if (emptyMsg) {
                emptyMsg.remove();
            }

            updateMergeButtonState();
        }

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

        function selectClassroom(id, name) {
            document.getElementById('select-filter-classroom').value = id;
            document.getElementById('btn-classroom-text').innerText = name;
            fetchMasterDiff(document.getElementById('current-merge-module').value);
        }

        function onSchoolFilterChange() {
            var schoolId = document.getElementById('select-filter-school').value;
            
            // Reset Classroom Selection
            document.getElementById('select-filter-classroom').value = '';
            document.getElementById('btn-classroom-text').innerText = 'Semua Kelas';

            // Filter the custom dropdown headers and columns
            document.querySelectorAll('.school-group-header').forEach(function(hdr) {
                if (!schoolId || hdr.getAttribute('data-school') === schoolId) {
                    hdr.style.display = '';
                } else {
                    hdr.style.display = 'none';
                }
            });

            filterBillTypesDropdown();
            fetchMasterDiff(document.getElementById('current-merge-module').value);
        }

        window.currentSyncItems = [];
window.currentSyncModule = '';
window.currentPage = 1;
window.itemsPerPage = 15;

function getMonthAbbr(m) {
    var map = { '7':'JUL', '8':'AGU', '9':'SEP', '10':'OKT', '11':'NOV', '12':'DES', '1':'JAN', '2':'FEB', '3':'MAR', '4':'APR', '5':'MEI', '6':'JUN' };
    return map[m] || m;
}

function renderMonthlyCards(info, isSynced = false) {
    var order = ['7','8','9','10','11','12','1','2','3','4','5','6'];
    var html = '<div class="d-flex flex-column gap-2">';
    
    // Master Row
    html += '<div class="d-flex align-items-center gap-1">';
    html += '<span class="badge bg-light text-dark me-2 w-75px fs-9 text-start">Lama</span>';
    order.forEach(function(m) {
        var isPaid = info.master.paid_months.indexOf(m) !== -1 || info.master.paid_months.indexOf(parseInt(m)) !== -1;
        var isUnpaid = info.master.unpaid_months.indexOf(m) !== -1 || info.master.unpaid_months.indexOf(parseInt(m)) !== -1;
        var bg = 'bg-light text-muted';
        if (isPaid) bg = 'bg-success text-white';
        else if (isUnpaid) bg = 'bg-light-danger text-danger';
        html += '<div class="badge rounded px-2 py-1 fs-9 fw-bolder ' + bg + '" style="width:32px;">' + getMonthAbbr(m) + '</div>';
    });
    html += '</div>';

    // Local Row
    html += '<div class="d-flex align-items-center gap-1">';
    html += '<span class="badge bg-light text-dark me-2 w-75px fs-9 text-start">Lokal</span>';
    if (info.local.is_empty) {
        html += '<span class="badge bg-light-danger text-danger px-2 py-1 fs-9 fw-bolder w-100 text-start">Belum Ada Record</span>';
    } else {
        order.forEach(function(m) {
            var isPaid = info.local.paid_months.indexOf(m) !== -1 || info.local.paid_months.indexOf(parseInt(m)) !== -1;
            var isUnpaid = info.local.unpaid_months.indexOf(m) !== -1 || info.local.unpaid_months.indexOf(parseInt(m)) !== -1;
            var bg = 'bg-light text-muted';
            if (isPaid) bg = isSynced ? 'text-white' : 'bg-success text-white';
            else if (isUnpaid) bg = 'bg-light-danger text-danger';
            var extraStyle = (isPaid && isSynced) ? 'background-color: #8b5cf6;' : '';
            html += '<div class="badge rounded px-2 py-1 fs-9 fw-bolder ' + bg + '" style="width:32px; ' + extraStyle + '">' + getMonthAbbr(m) + '</div>';
        });
    }
    html += '</div>';

    html += '</div>';
    return html;
}

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
            var tbody = document.getElementById('tbody-diff-preview');
    var thead = document.getElementById('thead-diff-preview');
    var scrollContainer = document.getElementById('table-scroll-container');
    var pagContainer = document.getElementById('pagination-container');
    
    if (window.currentSyncModule === 'billing_status') {
        scrollContainer.style.maxHeight = 'none';
        pagContainer.classList.remove('d-none');
        pagContainer.classList.add('d-flex');
        
        thead.innerHTML = '<tr class="text-start text-gray-500 fw-bolder text-uppercase tracking-wider">' +
            '<th class="w-40px px-3"><input class="form-check-input" type="checkbox" id="chk-all-diff" onchange="toggleAllDiffCheckboxes(this)"></th>' +
            '<th class="w-50px">No.</th>' +
            '<th>ID / Code</th>' +
            '<th>Nama Record</th>' +

'<th class="cursor-pointer text-primary text-center" onclick="sortSyncItemsBySyncStatus()" style="cursor: pointer;" title="Klik untuk mengurutkan berdasarkan Status Sinkronisasi">Status <i class="fas fa-sort ms-1" id="sort-icon-sync-status"></i></th>' +
'<th class="cursor-pointer text-primary" onclick="sortSyncItemsByStatus()" style="cursor: pointer;" title="Klik untuk mengurutkan Identik / Butuh Sync">Perbandingan Tagihan (Lama &rarr; Lokal) <i class="fas fa-sort ms-1" id="sort-icon-status"></i></th>' +
            '</tr>';
    } else {
        scrollContainer.style.maxHeight = '380px';
        pagContainer.classList.remove('d-flex');
        pagContainer.classList.add('d-none');
        
        thead.innerHTML = '<tr class="text-start text-gray-500 fw-bolder text-uppercase tracking-wider">' +
            '<th class="w-40px px-3"><input class="form-check-input" type="checkbox" id="chk-all-diff" onchange="toggleAllDiffCheckboxes(this)"></th>' +
            '<th>ID / Code</th>' +
            '<th>Nama Record</th>' +
            '<th>Status Mapping</th>' +
            '<th>Perbandingan Kolom (Aplikasi Lama &rarr; Lokal)</th>' +
            '</tr>';
    }

    var items = window.currentSyncItems;
    if (items.length === 0) {
        var colSpan = window.currentSyncModule === 'billing_status' ? 5 : 5;
        tbody.innerHTML = '<tr><td colspan="'+colSpan+'" class="text-center py-5 text-muted">Tidak ada data ditemukan untuk filter ini.</td></tr>';
        renderPaginationLinks(0);
        return;
    }

    var startIndex = 0;
    var endIndex = items.length;
    
    if (window.currentSyncModule === 'billing_status') {
        startIndex = (window.currentPage - 1) * window.itemsPerPage;
        endIndex = startIndex + window.itemsPerPage;
    }
    
    var paginatedItems = items.slice(startIndex, endIndex);
    var html = '';
    
    paginatedItems.forEach(function(item, idx) {
        var badgeClass = 'bg-light-info text-info';
        var badgeLabel = '🔵 100% IDENTIK';

        if (item.status === 'NEW_RECORD') {
            badgeClass = 'bg-light-success text-success';
            badgeLabel = '🟢 APLIKASI LAMA BARU';
        } else if (item.status === 'UPDATE_REQUIRED') {
            badgeClass = 'bg-light-warning text-warning';
            badgeLabel = '🟡 BUTUH UPDATE';
        } else if (item.status === 'CONFLICT') {
            badgeClass = 'bg-light-danger text-danger';
            badgeLabel = '🔴 KONFLIK';
        }

                var isSynced = (typeof window.recentlySyncedIds !== 'undefined') && window.recentlySyncedIds.includes(item.id);
        var cleanName = (item.name || '').replace(/\s*\[.*?\]\s*$/, '');
        
        var diffHtml = '';
        if (item.diffs && Object.keys(item.diffs).length > 0) {
            if (item.diffs['Status Tagihan Siswa'] && item.diffs['Status Tagihan Siswa'].type === 'monthly_cards') {
                diffHtml = renderMonthlyCards(item.diffs['Status Tagihan Siswa'], isSynced);
            } else {
                diffHtml = '<ul class="mb-0 ps-3 fs-8" style="color: #374151; font-weight: 500;">';
                for (var k in item.diffs) {
                    if (typeof item.diffs[k] === 'object') {
                        diffHtml += '<li class="my-1"><code class="text-primary fw-bolder px-1 py-0.5 bg-light-primary rounded" style="font-size: 11px;">' + k + '</code>: Aplikasi Lama (<span class="fw-bolder text-dark bg-light-warning text-warning px-1.5 py-0.5 rounded border border-warning border-opacity-25">"' + (item.diffs[k].master||'-') + '"</span>) vs Lokal (<span class="fw-bolder text-gray-800 bg-light px-1.5 py-0.5 rounded border border-gray-300">"' + (item.diffs[k].local||'-') + '"</span>)</li>';
                    } else {
                        diffHtml += '<li class="my-1"><span class="text-danger fw-bold">' + item.diffs[k] + '</span></li>';
                    }
                }
                diffHtml += '</ul>';
            }
        } else if (item.status === 'EXACT_MATCH') {
            diffHtml = '<span class="fw-semibold fs-8" style="color: #4b5563;">Data aplikasi lama dan lokal presisi identik</span>';
        }

        var statusBadge = '';
        if (window.currentSyncModule === 'billing_status') {
            if (item.status === 'EXACT_MATCH') {
                if (isSynced) {
                    statusBadge = '<span class="badge" style="background-color: #8b5cf6; color: white;"><i class="fas fa-check-circle text-white me-1"></i> Sukses Sinkronisasi</span>';
                } else {
                    statusBadge = '<span class="badge bg-light text-muted fw-bold border border-gray-300"><i class="fas fa-check text-muted me-1"></i> Tidak Perlu Sinkronisasi</span>';
                }
            } else if (item.status === 'NEW_RECORD') {
                statusBadge = '<span class="badge bg-light-primary text-primary fw-bolder px-2 py-1"><i class="fas fa-plus-circle text-primary me-1"></i> Belum Ada di Lokal</span>';
            } else if (item.status === 'UPDATE_REQUIRED') {
                statusBadge = '<span class="badge bg-light-warning text-warning fw-bolder px-2 py-1"><i class="fas fa-exclamation-triangle text-warning me-1"></i> Butuh Sync</span>';
            } else {
                statusBadge = '<span class="badge bg-light-danger text-danger fw-bolder px-2 py-1"><i class="fas fa-times-circle text-danger me-1"></i> Konflik</span>';
            }
        }

        var isCheckable = (item.status !== 'EXACT_MATCH');
        var checkAttr = isCheckable ? 'checked' : 'disabled';

        html += '<tr data-status="' + item.status + '">';
        html += '<td class="px-3"><input class="form-check-input chk-diff-item" type="checkbox" name="selected_ids[]" value="' + item.id + '" ' + checkAttr + ' onchange="updateMergeButtonState()"></td>';
        
        if (window.currentSyncModule === 'billing_status') {
            html += '<td class="fw-bold fs-7 text-muted">' + (startIndex + idx + 1) + '</td>';
            html += '<td class="fw-bold fs-7"><code>' + (item.code_or_nis || item.id) + '</code></td>';
            html += '<td class="fw-bolder text-dark">' + cleanName + '</td>';
            html += '<td class="text-center">' + statusBadge + '</td>';
        } else {
            html += '<td class="fw-bold fs-7"><code>' + (item.code_or_nis || item.id) + '</code></td>';
            html += '<td class="fw-bolder text-dark">' + item.name + '</td>';
            html += '<td><span class="badge ' + badgeClass + ' fw-bolder fs-8 px-2 py-1">' + badgeLabel + '</span></td>';
        }
        
        html += '<td>' + diffHtml + '</td>';
        html += '</tr>';
    });

    tbody.innerHTML = html;
    updateMergeButtonState();
    
    if (window.currentSyncModule === 'billing_status') {
        renderPaginationLinks(items.length);
    }
}

function renderPaginationLinks(totalItems) {
    var info = document.getElementById('pagination-info');
    var ul = document.getElementById('pagination-links');
    
    if (totalItems === 0) {
        info.innerText = 'Menampilkan 0-0 dari 0';
        ul.innerHTML = '';
        return;
    }
    
    var totalPages = Math.ceil(totalItems / window.itemsPerPage);
    var startIdx = (window.currentPage - 1) * window.itemsPerPage + 1;
    var endIdx = Math.min(startIdx + window.itemsPerPage - 1, totalItems);
    
    info.innerText = 'Menampilkan ' + startIdx + '-' + endIdx + ' dari ' + totalItems;
    
    var html = '';
    
    // Prev
    html += '<li class="page-item ' + (window.currentPage === 1 ? 'disabled' : '') + '">';
    html += '<a class="page-link" href="#" onclick="if(window.currentPage > 1) { window.currentPage--; renderCurrentPage(); } return false;"><i class="fas fa-chevron-left"></i></a>';
    html += '</li>';
    
    // Pages
    var startPage = Math.max(1, window.currentPage - 2);
    var endPage = Math.min(totalPages, startPage + 4);
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }
    
    for (var i = startPage; i <= endPage; i++) {
        html += '<li class="page-item ' + (window.currentPage === i ? 'active' : '') + '">';
        html += '<a class="page-link" href="#" onclick="window.currentPage = ' + i + '; renderCurrentPage(); return false;">' + i + '</a>';
        html += '</li>';
    }
    
    // Next
    html += '<li class="page-item ' + (window.currentPage === totalPages ? 'disabled' : '') + '">';
    html += '<a class="page-link" href="#" onclick="if(window.currentPage < ' + totalPages + ') { window.currentPage++; renderCurrentPage(); } return false;"><i class="fas fa-chevron-right"></i></a>';
    html += '</li>';
    
    ul.innerHTML = html;
}

function fetchMasterDiff(module, btnEl) {
    if (btnEl) {
        document.querySelectorAll('.btn-mod-tab').forEach(function(b) { b.classList.remove('active'); });
        btnEl.classList.add('active');
    }
    document.getElementById('current-merge-module').value = module;
    window.currentSyncModule = module;
    window.currentPage = 1;

    // Toggle filter container visibility
    var filterBox = document.getElementById('billing-filter-container');
    if (filterBox) {
        if (module === 'billing_status') {
            filterBox.classList.remove('d-none');
        } else {
            filterBox.classList.add('d-none');
        }
    }

    // Reset active filter
    filterDiffTable('ALL', null);

    var limitSelect = document.getElementById('select-diff-limit');
    var limitVal = limitSelect ? parseInt(limitSelect.value) : 50;

    var schoolId = document.getElementById('select-filter-school') ? document.getElementById('select-filter-school').value : '';
    var classroomId = document.getElementById('select-filter-classroom') ? document.getElementById('select-filter-classroom').value : '';
    var academicYearId = document.getElementById('select-filter-academic-year') ? document.getElementById('select-filter-academic-year').value : '';
    var billTypeId = document.getElementById('select-filter-bill-type') ? document.getElementById('select-filter-bill-type').value : '';

    if (document.getElementById('hidden_academic_year_id')) document.getElementById('hidden_academic_year_id').value = academicYearId;
    if (document.getElementById('hidden_bill_type_id')) document.getElementById('hidden_bill_type_id').value = billTypeId;

    var tbody = document.getElementById('tbody-diff-preview');
    
    // Setup initial header state if needed before load
    if (module === 'billing_status') {
        document.getElementById('table-scroll-container').style.maxHeight = 'none';
        document.getElementById('pagination-container').classList.remove('d-none');
        document.getElementById('pagination-container').classList.add('d-flex');
    } else {
        document.getElementById('table-scroll-container').style.maxHeight = '380px';
        document.getElementById('pagination-container').classList.remove('d-flex');
        document.getElementById('pagination-container').classList.add('d-none');
    }

    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Memuat analisis perbandingan module ' + module + ' (' + (limitVal >= 1000 ? 'Semua Data Master' : limitVal + ' Record') + ')...</td></tr>';

    axios.post('""', {
        module: module,
        limit: limitVal,
        school_id: schoolId,
        classroom_id: classroomId,
        academic_year_id: academicYearId,
        bill_type_id: billTypeId
    }).then(function(res) {
        var data = res.data;
        var summary = data.status_summary || {};

        document.getElementById('cnt-new').innerText = '🟢 Aplikasi Lama Baru: ' + (summary.new_count || 0);
        document.getElementById('cnt-update').innerText = '🟡 Butuh Update: ' + (summary.update_count || 0);
        document.getElementById('cnt-match').innerText = '🔵 100% Identik: ' + (summary.match_count || 0);
        document.getElementById('cnt-conflict').innerText = '🔴 Konflik Mapping: ' + (summary.conflict_count || 0);

        window.currentSyncItems = data.items || [];
        
        if (module === 'billing_status' && window.recentlySyncedIds && window.recentlySyncedIds.length > 0) {
            window.currentSyncStatusDirection = 'desc'; 
            sortSyncItemsBySyncStatus();
        } else {
            renderCurrentPage();
        }
    }).catch(function(err) {
        var errorMsg = err.message || 'Error Server';
        if (err.response && err.response.data && err.response.data.error) {
            errorMsg = err.response.data.error;
        }
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger fw-bolder bg-light-danger border border-danger border-opacity-25 rounded-3"><i class="fas fa-exclamation-triangle fs-2x mb-3 d-block text-danger"></i> ' + errorMsg + '</td></tr>';
        
        // Reset counters
        document.getElementById('cnt-new').innerText = '🟢 Aplikasi Lama Baru: 0';
        document.getElementById('cnt-update').innerText = '🟡 Butuh Update: 0';
        document.getElementById('cnt-match').innerText = '🔵 100% Identik: 0';
        document.getElementById('cnt-conflict').innerText = '🔴 Konflik Mapping: 0';
    });
}


        function toggleAllDiffCheckboxes(masterCb) {
            var items = document.querySelectorAll('.chk-diff-item:not(:disabled)');
            items.forEach(function(cb) {
                cb.checked = masterCb.checked;
            });
            updateMergeButtonState();
        }

        function updateMergeButtonState() {
            var selected = document.querySelectorAll('.chk-diff-item:checked');
            var btn = document.getElementById('btn-submit-merge');
            var txt = document.getElementById('txt-selected-count');

            if (txt) {
                txt.innerText = selected.length + ' record terverifikasi terpilih untuk di-merge';
            }

            if (btn) {
                btn.disabled = (selected.length === 0);
            }
        }
    