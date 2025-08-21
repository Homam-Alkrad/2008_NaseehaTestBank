<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Laravel Debug</h2>";

// Check if Laravel files exist
$files = [
    'vendor/autoload.php' => file_exists('vendor/autoload.php'),
    'bootstrap/app.php' => file_exists('bootstrap/app.php'),
    '.env' => file_exists('.env'),
    'storage' => is_dir('storage'),
    'bootstrap/cache' => is_dir('bootstrap/cache')
];

foreach ($files as $file => $exists) {
    echo ($exists ? '✅' : '❌') . " $file<br>";
}

// Try to load Laravel
if (file_exists('vendor/autoload.php')) {
    try {
        require_once 'vendor/autoload.php';
        echo "✅ Autoload OK<br>";
        
        if (file_exists('bootstrap/app.php')) {
            $app = require_once 'bootstrap/app.php';
            echo "✅ Laravel App OK<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
        echo "❌ File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    }
} else {
    echo "❌ Composer not installed<br>";
}
?>