<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

$oldH2 = '                Kategori 2: Missed Update (Sudah Bayar tapi Status Belum Terbayar)
                </h2>
            </div>';

$newH2 = '                Kategori 2: Missed Update (Sudah Bayar tapi Status Belum Terbayar)
                </h2>
                <button id="btn-fix-all-cat2" onclick="startMassiveFixCat2()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold px-5 py-2.5 rounded-xl transition-colors shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Perbaiki Semua Cat 2
                </button>
            </div>';

$c = str_replace($oldH2, $newH2, $c);

file_put_contents($f, $c);
echo "BUTTON INJECTED\n";
