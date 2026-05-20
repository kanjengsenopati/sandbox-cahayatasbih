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
        foreach ($this->scripts as $script) {
            $path = base_path($script);
            if (!\file_exists($path)) {
                $results[$script] = ['status' => -1, 'output' => "Script not found: $script"]; 
                continue;
            }

            // Capture output of eval in isolated scope
            \ob_start();
            $status = 0;
            try {
                $code = \file_get_contents($path);
                // Strip opening PHP tag if present
                $code = \preg_replace('/^<\?php/', '', $code);
                
                // Strip bootstrapping boilerplates
                $patterns = [
                    '/require(_once)?\s+[\'"]([^\'"]*\/)?(vendor\/)?autoload\.php[\'"];/',
                    '/require(_once)?\s+[\'"]([^\'"]*\/)?bootstrap\/app\.php[\'"];/',
                    '/\$app\s*=.*;/',
                    '/\$kernel\s*=.*;/',
                    '/\$kernel->bootstrap\(\);/',
                    '/\$response\s*=\s*\$kernel->handle\(.*\);/',
                ];
                $code = \preg_replace($patterns, '', $code);
                
                // Execute code in an isolated scope closure
                (function() use ($code) {
                    eval($code);
                })();
            } catch (\Throwable $e) {
                $status = 1;
                echo "\nException during execution:\n" . $e->getMessage() . "\n" . $e->getTraceAsString();
            }
            $output = \ob_get_clean();

            $results[$script] = [
                'status' => $status,
                'output' => $output,
            ];
        }
        return $results;
    }
}
