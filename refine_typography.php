<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

// 1. Refine "Periode"
$c = str_replace(
    'text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1',
    'text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-1',
    $c
);
$c = str_replace(
    'text-[10px] font-bold text-slate-400 uppercase tracking-wider',
    'text-[11px] font-bold text-slate-500 uppercase tracking-widest',
    $c
);
$c = str_replace(
    'text-[10px] font-bold text-red-500 uppercase tracking-wider mb-1',
    'text-[11px] font-bold text-red-500 uppercase tracking-widest mb-1',
    $c
);

// 2. Refine Periode value (e.g. Agt-2026)
// Wait, for Cat 5, it is: text-sm font-bold text-slate-800 mb-2
$c = str_replace(
    'text-sm font-bold text-slate-800 mb-2',
    'text-base font-extrabold text-slate-800 mb-2',
    $c
);
// For Cat 3: text-xs font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded
$c = str_replace(
    'text-xs font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded',
    'text-sm font-extrabold text-slate-800 bg-slate-100 px-2 py-0.5 rounded',
    $c
);

// 3. Refine Nominal:
$c = str_replace(
    'text-[10px] text-slate-500 mb-0.5',
    'text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-0.5',
    $c
);

// 4. Refine Amount value
$c = str_replace(
    'text-amount-error text-sm font-bold',
    'text-red-600 text-lg font-extrabold',
    $c
);
$c = str_replace(
    'text-amount-error text-xs font-bold mb-2',
    'text-red-600 text-lg font-extrabold mb-2',
    $c
);

// 5. Refine Petugas
// Cat 1, 2, 5
$c = str_replace(
    'text-[10px] bg-slate-50 border border-slate-100 px-2 py-1 rounded text-slate-600 truncate flex items-center gap-1.5',
    'text-[11px] font-bold bg-white border border-slate-200 px-2.5 py-1 rounded-md text-blue-600 truncate flex items-center gap-1.5 shadow-sm',
    $c
);
$c = str_replace(
    'text-[10px] bg-white border border-slate-200 px-2 py-1 rounded text-slate-700 truncate flex items-center gap-1.5',
    'text-[11px] font-bold bg-white border border-slate-200 px-2.5 py-1 rounded-md text-blue-600 truncate flex items-center gap-1.5 shadow-sm',
    $c
);

// Cat 3
$c = str_replace(
    'text-slate-600 font-medium flex items-center gap-1',
    'text-blue-600 font-bold text-[11px] flex items-center gap-1',
    $c
);
$c = str_replace(
    'text-slate-500 flex items-center gap-1',
    'text-slate-500 font-bold text-[11px] flex items-center gap-1',
    $c
);

// 6. Refine "UPDATE KOREKSI" button
$c = str_replace(
    'text-[10px] font-bold py-1.5',
    'text-[11px] font-bold py-2',
    $c
);

// 7. Refine "BATALKAN"
$c = str_replace(
    'text-xs font-bold px-4 py-2',
    'text-[11px] font-bold px-4 py-2 uppercase tracking-widest',
    $c
);

file_put_contents($f, $c);
echo "TYPOGRAPHY REFINED\n";
