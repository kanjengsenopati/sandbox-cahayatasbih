<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

$pattern = '/\$is_import = !empty\(\$item->import_log_id\);.*?\$petugas = \'Sistem\';\s*\}/s';

$replacement = <<<'EOD'
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

$c = preg_replace($pattern, $replacement, $c);
file_put_contents($f, $c);
echo "REPLACED\n";
