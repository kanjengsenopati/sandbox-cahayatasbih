<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

// Fix GROUP BY for q2
// old: GROUP BY b.id, s.name, c.name, bt.name, b.month, b.year, b.amount, t.paid_at, a.name, u.name, t.import_log_id, t.admin_id
$oldGroup = "GROUP BY b.id, s.name, c.name, bt.name, b.month, b.year, b.amount, t.paid_at, a.name, u.name, t.import_log_id, t.admin_id";
$newGroup = "GROUP BY b.id, s.name, c.name, bt.name, b.month, b.year, b.amount, t.paid_at, a.name, u_admin.name, u_parent.name, t.import_log_id, t.admin_id, t.user_id";

$c = str_replace($oldGroup, $newGroup, $c);
file_put_contents($f, $c);
echo "GROUP_BY_FIXED\n";
