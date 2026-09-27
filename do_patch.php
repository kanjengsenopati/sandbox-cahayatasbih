<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

// 1. Inject recentlySyncedIds
if (strpos($content, 'window.recentlySyncedIds') === false) {
    $searchJs = "<script>";
    $replaceJs = "<script>\n    window.recentlySyncedIds = @json(session('synced_ids', []));";
    $content = str_replace($searchJs, $replaceJs, $content);
}

// 2. Modify renderMonthlyCards
$searchCards = <<<EOD
function renderMonthlyCards(info) {
EOD;
$replaceCards = <<<EOD
function renderMonthlyCards(info, isSynced = false) {
EOD;
$content = str_replace($searchCards, $replaceCards, $content);

$searchLocalRow = <<<EOD
            var isPaid = info.local.paid_months.indexOf(m) !== -1 || info.local.paid_months.indexOf(parseInt(m)) !== -1;
            var isUnpaid = info.local.unpaid_months.indexOf(m) !== -1 || info.local.unpaid_months.indexOf(parseInt(m)) !== -1;
            var bg = 'bg-light text-muted';
            if (isPaid) bg = 'bg-success text-white';
            else if (isUnpaid) bg = 'bg-light-danger text-danger';
            html += '<div class="badge rounded px-2 py-1 fs-9 fw-bolder ' + bg + '" style="width:32px;">' + getMonthAbbr(m) + '</div>';
EOD;

$replaceLocalRow = <<<EOD
            var isPaid = info.local.paid_months.indexOf(m) !== -1 || info.local.paid_months.indexOf(parseInt(m)) !== -1;
            var isUnpaid = info.local.unpaid_months.indexOf(m) !== -1 || info.local.unpaid_months.indexOf(parseInt(m)) !== -1;
            var bg = 'bg-light text-muted';
            if (isPaid) bg = isSynced ? 'text-white' : 'bg-success text-white';
            else if (isUnpaid) bg = 'bg-light-danger text-danger';
            var extraStyle = (isPaid && isSynced) ? 'background-color: #8b5cf6;' : '';
            html += '<div class="badge rounded px-2 py-1 fs-9 fw-bolder ' + bg + '" style="width:32px; ' + extraStyle + '">' + getMonthAbbr(m) + '</div>';
EOD;

$content = str_replace(str_replace("\r\n", "\n", $searchLocalRow), $replaceLocalRow, str_replace("\r\n", "\n", $content));

// 3. Update thead
$searchThead = <<<EOD
            thead.innerHTML = '<tr class="text-start text-gray-500 fw-bolder text-uppercase tracking-wider">' +
                '<th class="w-40px px-3"><input class="form-check-input" type="checkbox" id="chk-all-diff" onchange="toggleAllDiffCheckboxes(this)"></th>' +
                '<th class="w-50px">No.</th>' +
                '<th>ID / Code</th>' +
                '<th>Nama Record</th>' +
                '<th class="cursor-pointer text-primary" onclick="sortSyncItemsByStatus()" style="cursor: pointer;" title="Klik untuk mengurutkan Identik / Butuh Sync">Perbandingan Status Tagihan (Aplikasi Lama &rarr; Lokal) <i class="fas fa-sort ms-1" id="sort-icon-status"></i></th>' +
                '</tr>';
EOD;

$replaceThead = <<<EOD
            thead.innerHTML = '<tr class="text-start text-gray-500 fw-bolder text-uppercase tracking-wider">' +
                '<th class="w-40px px-3"><input class="form-check-input" type="checkbox" id="chk-all-diff" onchange="toggleAllDiffCheckboxes(this)"></th>' +
                '<th class="w-50px">No.</th>' +
                '<th>ID / Code</th>' +
                '<th>Nama Record</th>' +
                '<th class="text-center">Status</th>' +
                '<th class="cursor-pointer text-primary" onclick="sortSyncItemsByStatus()" style="cursor: pointer;" title="Klik untuk mengurutkan Identik / Butuh Sync">Perbandingan Status Tagihan (Aplikasi Lama &rarr; Lokal) <i class="fas fa-sort ms-1" id="sort-icon-status"></i></th>' +
                '</tr>';
EOD;
$content = str_replace(str_replace("\r\n", "\n", $searchThead), $replaceThead, str_replace("\r\n", "\n", $content));

// 4. Update row rendering in loop
$patternRender = '/var diffHtml = \'\';.*?if \(window.currentSyncModule !== \'billing_status\'\) \{.*?html \+= \'<td>\' \+ diffHtml \+ \'<\/td>\';\s*html \+= \'<\/tr>\';/s';

$replaceRender = <<<EOD
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
EOD;

$content = preg_replace($patternRender, $replaceRender, $content, 1);

file_put_contents('resources/views/admins/admin/audit/sync.blade.php', $content);
echo "Final patch applied!\n";
