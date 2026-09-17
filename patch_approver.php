<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

// 1. Fix SQL Queries
// Replace `LEFT JOIN users u ON u.id=t.admin_id` with proper joins and select
// In $q2, $q3, $q6
function fixQuery($sql) {
    // Replace SELECT ... a.name AS admin_petugas, u.name AS user_petugas
    $sql = preg_replace(
        '/a\.name\s+AS\s+admin_petugas,\s*u\.name\s+AS\s+user_petugas,\s*t\.import_log_id,\s*t\.admin_id/',
        'COALESCE(a.name, u_admin.name) AS admin_petugas, u_parent.name AS user_petugas, t.import_log_id, t.admin_id, t.user_id',
        $sql
    );
    // Replace the JOINS
    $sql = preg_replace(
        '/LEFT JOIN admins a ON a\.id\s*=\s*t\.admin_id\s*LEFT JOIN users u ON u\.id\s*=\s*t\.admin_id/',
        "LEFT JOIN admins a ON a.id = t.admin_id\n      LEFT JOIN users u_admin ON u_admin.id = t.admin_id\n      LEFT JOIN users u_parent ON u_parent.id = t.user_id",
        $sql
    );
    return $sql;
}

// Manually fix each query to be safe, because the exact spacing might differ.
$oldSelect = "a.name AS admin_petugas, u.name AS user_petugas, t.import_log_id, t.admin_id";
$newSelect = "COALESCE(a.name, u_admin.name) AS admin_petugas, u_parent.name AS user_petugas, t.import_log_id, t.admin_id, t.user_id";

$oldJoin = "LEFT JOIN admins a ON a.id = t.admin_id\n    LEFT JOIN users u ON u.id=t.admin_id";
$newJoin = "LEFT JOIN admins a ON a.id = t.admin_id\n    LEFT JOIN users u_admin ON u_admin.id = t.admin_id\n    LEFT JOIN users u_parent ON u_parent.id = t.user_id";
// actually just preg replace because of spacing issues
$c = preg_replace('/a\.name AS admin_petugas,\s*u\.name AS user_petugas,\s*t\.import_log_id,\s*t\.admin_id/', $newSelect, $c);
$c = preg_replace('/LEFT JOIN admins a ON a\.id\s*=\s*t\.admin_id\s*LEFT JOIN users u ON u\.id=t\.admin_id/', $newJoin, $c);

// 2. Fix PHP Logic
// Replace the block setting entry_method and petugas
$oldPHP = <<<'EOD'
                                        $is_import = !empty($item->import_log_id);
                                        $is_real_admin = !empty($item->admin_petugas);
                                        $is_parent = !empty($item->user_petugas);
                                        
                                        if ($is_import) {
                                            $entry_method = 'Import Data (Legal)';
                                            $entry_color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                            $petugas = 'Excel Importer';
                                        } elseif ($is_real_admin) {
                                            $entry_method = 'Aksi User (Legal)';
                                            $entry_color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                            $petugas = $item->admin_petugas;
                                        } elseif ($is_parent) {
                                            $entry_method = 'Approval Aplikasi';
                                            $entry_color = 'bg-blue-100 text-blue-700 border border-blue-200';
                                            $petugas = $item->user_petugas . ' (Ortu)';
                                        } else {
                                            $entry_method = 'Auto/Unknown';
                                            $entry_color = 'bg-slate-100 text-slate-700 border border-slate-200';
                                            $petugas = 'Sistem';
                                        }
EOD;

$newPHP = <<<'EOD'
                                        $is_import = !empty($item->import_log_id);
                                        $has_parent = !empty($item->user_id);
                                        $admin_name = !empty($item->admin_petugas) ? $item->admin_petugas : 'Sistem / Admin';
                                        
                                        if ($is_import) {
                                            $entry_method = 'Import Data (Legal)';
                                            $entry_color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                            $petugas = 'Excel Importer';
                                        } elseif ($has_parent) {
                                            $entry_method = 'Approval Aplikasi';
                                            $entry_color = 'bg-blue-100 text-blue-700 border border-blue-200';
                                            $petugas = $admin_name;
                                        } else {
                                            $entry_method = 'Aksi User (Legal)';
                                            $entry_color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                            $petugas = $admin_name;
                                        }
EOD;

$c = str_replace($oldPHP, $newPHP, $c);

file_put_contents($f, $c);
echo "SQL AND LOGIC PATCHED\n";
