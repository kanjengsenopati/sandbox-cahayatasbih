<?php
header('Content-Type: text/plain');
echo "Current server directory:\n";
echo __DIR__ . "\n\n";

echo "Git Status:\n";
echo shell_exec('git status 2>&1') . "\n";

echo "Git Latest Commits:\n";
echo shell_exec('git log -n 5 --oneline 2>&1') . "\n";

echo "Whoami:\n";
echo shell_exec('whoami 2>&1') . "\n";
