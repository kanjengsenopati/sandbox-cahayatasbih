<?php

namespace App\Services;

class AuditService
{
    /**
     * List of audit script filenames (relative to project root).
     * Order matters if some scripts depend on previous ones.
     */
    protected $scripts = [
        'audit_ghost_timestamps.php',
        'cleanup_ghost_bills.php',
        'find_ghost_bills.php',
        'find_duplicate_bill_types.php',
        'check_image.php',
        'check_avatars.php',
        'check_bills.php',
        // add other audit scripts as needed
    ];

    /**
     * Run all audit scripts and capture their output.
     *
     * @return array  ['script' => ['status' => int, 'output' => string]]
     */
    public function runAll(): array
    {
        $results = [];
        $php = (string) \trim(\shell_exec('which php'));
        if (empty($php)) {
            // fallback to php in PATH
            $php = 'php';
        }
        foreach ($this->scripts as $script) {
            $path = base_path($script);
            if (!\file_exists($path)) {
                $results[$script] = ['status' => -1, 'output' => "Script not found: $script"]; 
                continue;
            }
            // Execute script and capture output & exit code
            $command = "$php \"$path\" 2>&1";
            $output = [];
            $status = 0;
            \exec($command, $output, $status);
            $results[$script] = [
                'status' => $status,
                'output' => \implode("\n", $output),
            ];
        }
        return $results;
    }
}
