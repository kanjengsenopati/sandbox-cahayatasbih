<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

$histUIOld = <<<'EOD'
            <div>
                <?php if(count($history_repairs) > 0): ?>
                    <?php foreach($history_repairs as $r): ?>
                    <div class="student-wrapper-riwayat border-b border-slate-100 p-5 hover:bg-slate-50/50 flex justify-between items-center" data-search="<?= strtolower(htmlspecialchars($r->santri.' '.$r->kelas.' '.$r->tagihan)) ?>">
                        <div>
                            <div class="text-sm font-bold text-slate-800"><?= $r->santri ?> <span class="text-xs text-slate-400 font-normal ml-2">Kelas <?= $r->kelas ?></span></div>
                            <div class="text-xs text-slate-500 mt-1"><?= $r->tagihan ?> (<?= $indo_months[(int)$r->month] ?>-<?= $r->year ?>) - Rp <?= number_format($r->amount, 0, ',', '.') ?></div>
                            <div class="text-[10px] text-slate-400 mt-1">
                                Kategori Anomali: <span class="font-bold text-slate-600"><?= strtoupper($r->category) ?></span> | Waktu Fix: <?= date('d/m/Y H:i', strtotime($r->repair_time)) ?>
                            </div>
                        </div>
                        <button onclick="revertRepair('<?= $r->repair_id ?>', this)" class="bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 text-[11px] font-bold px-4 py-2 capitalize tracking-widest rounded-lg transition-colors flex items-center gap-1 shadow-sm">
                            Batalkan
                        </button>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-8 text-center text-slate-500 italic">Belum ada riwayat perbaikan.</div>
                <?php endif; ?>
            </div>
EOD;

$histUINew = <<<'EOD'
            <div>
                <?php if(count($g_history) > 0): ?>
                    <?php $no = 1; foreach($g_history as $g):?>
                    <div class="student-wrapper-riwayat border-b border-slate-100 p-5 hover:bg-slate-50/50" data-search="<?= strtolower(htmlspecialchars($g['santri'].' '.$g['kelas'])) ?>">
                        <?php $stu_hash = md5('hist'.$g['santri'].$g['kelas']); ?>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-slate-800 text-lg capitalize">
                                <?= $no++ ?>. <?= htmlspecialchars(ucwords(strtolower($g['santri']))) ?> <span class="text-sm text-slate-500 font-normal ml-2 capitalize">(Kelas: <?= htmlspecialchars(ucwords(strtolower($g['kelas']))) ?>)</span>
                            </h3>
                            <button onclick="revertMultiRepair('<?= $stu_hash ?>', this)" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-[11px] font-bold px-4 py-2 tracking-widest rounded-lg transition-colors shadow-sm hidden btn-bulk-<?= $stu_hash ?>">
                                Batalkan Terpilih (<span class="count-<?= $stu_hash ?>">0</span>)
                            </button>
                        </div>
                        <div class="space-y-5 pl-4 border-l-2 border-slate-100 ml-2">
                            <?php foreach($g['tagihan_groups'] as $tagihan_name => $items):?>
                            <div>
                                <?php $tag_hash = md5('hist'.$g['santri'].$tagihan_name); ?>
                                <div class="flex items-center gap-2 mb-3 mt-2">
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                    <h4 class="text-sm font-bold text-slate-600 capitalize tracking-wider">
                                        <?= htmlspecialchars(ucwords(strtolower($tagihan_name))) ?>
                                    </h4>
                                    <label class="ml-auto text-[11px] font-bold text-slate-500 flex items-center gap-1.5 cursor-pointer hover:text-blue-600">
                                        <input type="checkbox" onchange="toggleSelectAll('<?= $tag_hash ?>', this.checked, '<?= $stu_hash ?>')" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        Pilih Semua
                                    </label>
                                </div>
                                <!-- Grid Cards -->
                                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                    <?php foreach($items as $item): ?>
                                    <div class="border border-slate-200 rounded-xl p-4 bg-white shadow-[0_2px_8px_rgb(0,0,0,0.04)] flex flex-col justify-between relative">
                                        <input type="checkbox" value="<?= $item->repair_id ?>" class="chk-<?= $tag_hash ?> chk-group-<?= $stu_hash ?> w-4 h-4 text-blue-600 rounded border-slate-300 absolute top-3 right-3 cursor-pointer shadow-sm" onchange="updateBulkCount('<?= $stu_hash ?>')">
                                        <div>
                                            <div class="text-[11px] font-bold text-slate-500 capitalize tracking-widest mb-1">Periode</div>
                                            <div class="text-sm font-bold text-slate-800 mb-2 capitalize"><?= $indo_months[(int)$item->month] ?> <?= $item->year ?></div>
                                            <div class="text-[11px] font-bold text-slate-400 capitalize tracking-widest mb-0.5">Nominal</div>
                                            <div class="text-slate-700 text-sm font-bold">Rp <?= number_format($item->amount,0,',','.')?></div>
                                        </div>
                                        <div class="mt-3 pt-3 border-t border-slate-100 flex flex-col gap-1.5">
                                            <div class="text-[10px] text-slate-400">
                                                Anomali: <span class="font-bold text-slate-600 capitalize"><?= htmlspecialchars(ucwords(strtolower($item->category))) ?></span><br>
                                                Waktu: <?= date('d/m/Y H:i', strtotime($item->repair_time)) ?>
                                            </div>
                                            <button onclick="revertRepair('<?= $item->repair_id ?>', this)" class="mt-2 w-full bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-[11px] font-bold py-2 rounded transition-colors shadow-sm flex items-center justify-center gap-1 capitalize">Batalkan</button>
                                        </div>
                                    </div>
                                    <?php endforeach;?>
                                </div>
                            </div>
                            <?php endforeach;?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-8 text-center text-slate-500 italic">Belum ada riwayat perbaikan.</div>
                <?php endif; ?>
            </div>
EOD;

$c = str_replace($histUIOld, $histUINew, $c);

file_put_contents($f, $c);
echo "HISTORY_UI_PATCHED\n";
