<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

// Insert progress bar after the amber info box closing div
$infoBoxEnd = "bukan transaksinya.</span></p>\n            </div>";

$progressBarHtml = <<<'EOD'
bukan transaksinya.</span></p>
            </div>
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

$c = str_replace($infoBoxEnd, $progressBarHtml, $c);

file_put_contents($f, $c);
echo "PROGRESS BAR INJECTED\n";
