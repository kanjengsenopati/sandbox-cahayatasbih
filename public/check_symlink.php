<?php
header('Content-Type: text/plain');

echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Current File: " . __FILE__ . "\n";

$storageSymlink = __DIR__ . '/storage';
echo "Storage Symlink Path: " . $storageSymlink . "\n";

if (is_link($storageSymlink)) {
    echo "Is a symlink: Yes\n";
    $target = readlink($storageSymlink);
    echo "Symlink Target: " . $target . "\n";
} else {
    echo "Is a symlink: No\n";
    $target = '';
}

echo "Exists check on public/storage: " . (file_exists($storageSymlink) ? 'Yes' : 'No') . "\n";

$expectedTarget = realpath(__DIR__ . '/../storage/app/public');
echo "Expected Target: " . $expectedTarget . "\n";

if (isset($_GET['recreate'])) {
    echo "\n--- Recreating Symlink ---\n";
    if (file_exists($storageSymlink) || is_link($storageSymlink)) {
        echo "Deleting old storage link/folder...\n";
        if (is_link($storageSymlink)) {
            unlink($storageSymlink);
        } else {
            // It's a directory, maybe we shouldn't delete if it's a real directory unless empty
            rmdir($storageSymlink);
        }
    }
    
    if (symlink($expectedTarget, $storageSymlink)) {
        echo "Symlink created successfully!\n";
    } else {
        echo "Failed to create symlink. Trying with relative path...\n";
        // Try relative link
        if (symlink('../storage/app/public', $storageSymlink)) {
            echo "Relative symlink created successfully!\n";
        } else {
            echo "Failed to create relative symlink too.\n";
        }
    }
    
    // Refresh info
    if (is_link($storageSymlink)) {
        echo "New Symlink Target: " . readlink($storageSymlink) . "\n";
        echo "New Exists check: " . (file_exists($storageSymlink) ? 'Yes' : 'No') . "\n";
    }
}

// List some files in storage/app/public/images/officers if possible
$realStoragePath = realpath(__DIR__ . '/../storage/app/public/images/officers');
echo "\nReal Storage Path: " . $realStoragePath . "\n";
if ($realStoragePath && is_dir($realStoragePath)) {
    echo "Files in officers storage:\n";
    $files = scandir($realStoragePath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo " - " . $file . " (size: " . filesize($realStoragePath . '/' . $file) . " bytes)\n";
        }
    }
} else {
    echo "Real storage path not found or not a directory\n";
}
