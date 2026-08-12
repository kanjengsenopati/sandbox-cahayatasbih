<?php
$pdo = new PDO('sqlite:database/local_replica.sqlite');
$stmt = $pdo->query("SELECT id, created_at, amount, balance_before, balance_after, status, type, description FROM saldo_histories WHERE student_id = '5d51813e-05b1-49ac-8806-41ab4dfa6a72' ORDER BY created_at DESC LIMIT 10");
foreach($stmt as $row) {
    echo $row['created_at'] . ' | TYPE: ' . $row['type'] . ' | AMT: ' . $row['amount'] . ' | BEF: ' . $row['balance_before'] . ' | AFT: ' . $row['balance_after'] . ' | DESC: ' . substr($row['description'], 0, 30) . PHP_EOL;
}
