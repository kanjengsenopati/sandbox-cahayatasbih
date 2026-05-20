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
                
                // Strip bootstrapping boilerplates line-by-line
                $lines = \explode("\n", $code);
                $filteredLines = [];
                foreach ($lines as $line) {
                    // Skip the opening php tag and the Laravel bootstrapper lines
                    if (\preg_match('/^<\?php/', \trim($line)) ||
                        \str_contains($line, 'autoload.php') ||
                        \str_contains($line, 'bootstrap/app.php') ||
                        \str_contains($line, 'make(Illuminate\Contracts') ||
                        \str_contains($line, '->bootstrap()') ||
                        \str_contains($line, '->handle(')) {
                        continue;
                    }
                    $filteredLines[] = $line;
                }
                $code = \implode("\n", $filteredLines);
                
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
