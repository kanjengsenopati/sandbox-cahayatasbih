<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

// =============================================
// 2. ADD UI BUTTON + PROGRESS BAR to CAT2 header
// =============================================
$oldCat2Header = <<<'EOD'
            <h2 class="text-h2 text-amber-600 flex items-center gap-2">
                <span class="bg-amber-100 p-1.5 rounded-full"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                Kategori 2: Missed Update (Sudah Bayar tapi Status Belum Terbayar)
            </h2>
EOD;

$newCat2Header = <<<'EOD'
            <h2 class="text-h2 text-amber-600 flex items-center gap-2">
                <span class="bg-amber-100 p-1.5 rounded-full"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                Kategori 2: Missed Update (Sudah Bayar tapi Status Belum Terbayar)
            </h2>
            <button id="btn-fix-all-cat2" onclick="startMassiveFixCat2()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold px-5 py-2.5 rounded-xl transition-colors shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Perbaiki Semua Cat 2
            </button>
EOD;
$c = str_replace($oldCat2Header, $newCat2Header, $c);

// =============================================
// 3. ADD PROGRESS BAR HTML (after cat2 info box)
// =============================================
$oldInfoBox = 'Disebabkan oleh:';
// Find the closing div of the info box and add progress bar after it
$progressBarHtml = <<<'EOD'
<!-- Massive Fix Progress Bar -->
        <div id="massive-fix-progress" class="hidden px-6 py-4 bg-white border-b border-slate-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-bold text-slate-700" id="progress-label">Mempersiapkan...</span>
                <span class="text-sm font-bold text-emerald-600" id="progress-pct">0%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                <div id="progress-bar" class="bg-emerald-500 h-3 rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
            </div>
            <div class="mt-2 text-xs text-slate-500" id="progress-detail"></div>
        </div>
EOD;

// Insert progress bar after the amber info box
$infoBoxEnd = "bukan transaksinya.</span></p>\n            </div>";
$c = str_replace($infoBoxEnd, $infoBoxEnd . "\n" . $progressBarHtml, $c);

// =============================================
// 4. ADD JS LOGIC for chunked batch processing
// =============================================
$jsInsertBefore = "function filterStudents(tab)";

$massiveFixJs = <<<'JSEOF'
async function startMassiveFixCat2() {
    const btn = document.getElementById('btn-fix-all-cat2');
    
    // Step 1: Pre-count
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Menghitung...';
    btn.disabled = true;
    
    try {
        const countData = new FormData();
        countData.append('action', 'count_cat2');
        const countRes = await fetch('audit_vps.php', { method: 'POST', body: countData });
        const countJson = await countRes.json();
        
        if (!countJson.success || countJson.total === 0) {
            alert('Tidak ada anomali CAT2 yang perlu diperbaiki.');
            btn.innerHTML = 'Perbaiki Semua Cat 2';
            btn.disabled = false;
            return;
        }
        
        // Step 2: Confirmation
        const total = countJson.total;
        if (!confirm(
            'MASSIVE BULK FIX — Kategori 2\n\n' +
            'Total tagihan anomali: ' + total + ' bills\n' +
            'Proses akan berjalan dalam batch @50 tagihan.\n\n' +
            'Setiap batch memiliki snapshot rollback.\n' +
            'Lanjutkan?'
        )) {
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Perbaiki Semua Cat 2';
            btn.disabled = false;
            return;
        }
        
        // Step 3: Show progress bar
        const progressEl = document.getElementById('massive-fix-progress');
        const progressBar = document.getElementById('progress-bar');
        const progressLabel = document.getElementById('progress-label');
        const progressPct = document.getElementById('progress-pct');
        const progressDetail = document.getElementById('progress-detail');
        progressEl.classList.remove('hidden');
        btn.classList.add('hidden');
        
        // Step 4: Chunked processing loop
        let totalProcessed = 0;
        let batchNum = 0;
        let remaining = total;
        
        while (remaining > 0) {
            batchNum++;
            progressLabel.textContent = 'Batch ' + batchNum + ': Memproses ' + Math.min(50, remaining) + ' tagihan...';
            
            const batchData = new FormData();
            batchData.append('action', 'fix_all_cat2');
            batchData.append('offset', '0');
            batchData.append('limit', '50');
            
            const batchRes = await fetch('audit_vps.php', { method: 'POST', body: batchData });
            const batchJson = await batchRes.json();
            
            if (!batchJson.success) {
                progressBar.classList.remove('bg-emerald-500');
                progressBar.classList.add('bg-red-500');
                progressLabel.textContent = 'Error pada Batch ' + batchNum + ': ' + batchJson.message;
                progressDetail.innerHTML = '<button onclick="startMassiveFixCat2()" class="mt-2 bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-lg">Retry dari Batch ' + batchNum + '</button>';
                return;
            }
            
            totalProcessed += batchJson.processed;
            remaining = batchJson.remaining;
            
            const pct = Math.round(((total - remaining) / total) * 100);
            progressBar.style.width = pct + '%';
            progressPct.textContent = pct + '%';
            progressDetail.textContent = totalProcessed + ' / ' + total + ' tagihan selesai. Sisa: ' + remaining;
            
            if (batchJson.processed === 0) break;
        }
        
        // Step 5: Complete
        progressBar.style.width = '100%';
        progressBar.classList.remove('bg-emerald-500');
        progressBar.classList.add('bg-emerald-400');
        progressPct.textContent = '100%';
        progressLabel.textContent = 'Selesai! ' + totalProcessed + ' tagihan berhasil diperbaiki.';
        progressDetail.textContent = 'Halaman akan dimuat ulang dalam 3 detik...';
        
        setTimeout(() => { location.reload(); }, 3000);
        
    } catch (err) {
        alert('Kesalahan jaringan: ' + err.message);
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Perbaiki Semua Cat 2';
        btn.disabled = false;
    }
}

JSEOF;

$c = str_replace($jsInsertBefore, $massiveFixJs . "\n" . $jsInsertBefore, $c);

file_put_contents($f, $c);
echo "FRONTEND UI + JS ADDED\n";
