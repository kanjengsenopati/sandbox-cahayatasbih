<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

// =============================================
// FIX 1: SQL QUERY REWRITE
// =============================================
// Problem: COALESCE(a.name, u_admin.name) falls back to users table when admin_id 
// actually stores a user_id (parent). We must ONLY use admins table for admin_id.
// For import transactions, join import_logs to get the real admin.

// Fix q2 SELECT and JOINs
$oldQ2Select = "COALESCE(a.name, u_admin.name) AS admin_petugas, u_parent.name AS user_petugas, t.import_log_id, t.admin_id, t.user_id";
$newQ2Select = "a.name AS admin_petugas, il_admin.name AS import_admin, t.import_log_id, t.admin_id, t.user_id";
$c = str_replace($oldQ2Select, $newQ2Select, $c);

$oldQ2Join = "LEFT JOIN admins a ON a.id = t.admin_id\n    LEFT JOIN users u_admin ON u_admin.id = t.admin_id\n    LEFT JOIN users u_parent ON u_parent.id = t.user_id";
$newQ2Join = "LEFT JOIN admins a ON a.id = t.admin_id\n    LEFT JOIN import_logs il ON il.id = t.import_log_id\n    LEFT JOIN admins il_admin ON il_admin.id = il.admin_id";
$c = str_replace($oldQ2Join, $newQ2Join, $c);

// Fix GROUP BY
$oldGroupBy = "GROUP BY b.id, s.name, c.name, bt.name, b.month, b.year, b.amount, t.paid_at, a.name, u_admin.name, u_parent.name, t.import_log_id, t.admin_id, t.user_id";
$newGroupBy = "GROUP BY b.id, s.name, c.name, bt.name, b.month, b.year, b.amount, t.paid_at, a.name, il_admin.name, t.import_log_id, t.admin_id, t.user_id";
$c = str_replace($oldGroupBy, $newGroupBy, $c);

// =============================================
// FIX 2: PHP LOGIC REWRITE
// =============================================
// Replace all 3 instances of the entry_method logic block
$oldLogic = <<<'EOD'
$is_import = !empty($item->import_log_id);
                                        $has_parent = !empty($item->user_id);
                                        $admin_name = !empty($item->admin_petugas) ? ucwords(strtolower($item->admin_petugas)) : 'Sistem / Admin';
                                        
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

$newLogic = <<<'EOD'
$is_import = !empty($item->import_log_id);
                                        $real_admin = !empty($item->admin_petugas) ? ucwords(strtolower($item->admin_petugas)) : null;
                                        $import_admin = !empty($item->import_admin) ? ucwords(strtolower($item->import_admin)) : null;
                                        
                                        if ($is_import) {
                                            $entry_method = 'Import Data (Legal)';
                                            $entry_color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                            $petugas = $import_admin ?? $real_admin ?? 'Sistem / Admin';
                                        } elseif ($real_admin) {
                                            $entry_method = 'Aksi User (Legal)';
                                            $entry_color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                            $petugas = $real_admin;
                                        } else {
                                            $entry_method = 'Approval Aplikasi';
                                            $entry_color = 'bg-blue-100 text-blue-700 border border-blue-200';
                                            $petugas = 'Sistem / Admin';
                                        }
EOD;

$c = str_replace($oldLogic, $newLogic, $c);

file_put_contents($f, $c);

// Verify replacements
$count_new_logic = substr_count($c, '$import_admin = !empty($item->import_admin)');
$count_old_logic = substr_count($c, '$has_parent = !empty($item->user_id)');
echo "NEW LOGIC INSTANCES: $count_new_logic (expected 3)\n";
echo "OLD LOGIC INSTANCES: $count_old_logic (expected 0)\n";
echo "PATCH COMPLETE\n";
