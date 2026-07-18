<?php

header('Content-Type: text/plain; charset=utf-8');

echo "==========================================\n";
echo "       HASSA POS LOGO DIAGNOSTIC          \n";
echo "==========================================\n\n";

// Load Laravel Bootstrap
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$business = null;
try {
    $business = \App\Business::first();
} catch (\Exception $e) {
    echo "Database query error (this is normal if DB is offline): " . $e->getMessage() . "\n\n";
}

echo "Laravel Environment:\n";
echo "APP_ENV: " . env('APP_ENV') . "\n";
echo "APP_URL: " . env('APP_URL') . "\n";
echo "FILESYSTEM_DISK: " . env('FILESYSTEM_DISK', 'not set (defaults to local)') . "\n";
echo "Default Disk in Config: " . config('filesystems.default') . "\n\n";

echo "Business Info:\n";
if ($business) {
    echo "ID: " . $business->id . "\n";
    echo "Name: " . $business->name . "\n";
    echo "Logo in DB: " . $business->logo . "\n";
    echo "Logo URL (url()): " . url('uploads/business_logos/' . $business->logo) . "\n";
    echo "Logo URL (asset()): " . asset('uploads/business_logos/' . $business->logo) . "\n";
} else {
    echo "Could not load business from database.\n";
}
echo "\n";

echo "Directories and Permissions:\n";
$dirs = [
    'public/uploads' => __DIR__ . '/uploads',
    'public/uploads/business_logos' => __DIR__ . '/uploads/business_logos',
    'storage/app/public' => __DIR__ . '/../storage/app/public',
    'storage/app/public/business_logos' => __DIR__ . '/../storage/app/public/business_logos',
];

foreach ($dirs as $name => $path) {
    echo "$name:\n";
    echo "  Path: $path\n";
    echo "  Exists: " . (file_exists($path) ? 'Yes' : 'No') . "\n";
    echo "  Is Directory: " . (is_dir($path) ? 'Yes' : 'No') . "\n";
    echo "  Is Writable: " . (is_writable($path) ? 'Yes' : 'No') . "\n";
    if (file_exists($path) && is_dir($path)) {
        echo "  Permissions: " . substr(sprintf('%o', fileperms($path)), -4) . "\n";
        echo "  Files inside:\n";
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $path . '/' . $file;
                echo "    - $file (" . filesize($filePath) . " bytes, is_readable: " . (is_readable($filePath) ? 'Yes' : 'No') . ")\n";
            }
        }
    }
    echo "\n";
}

echo "Check for Root Symlink/Htaccess:\n";
echo "Root uploads symlink/directory exists: " . (file_exists(__DIR__ . '/../uploads') ? 'Yes' : 'No') . "\n";
if (file_exists(__DIR__ . '/../uploads')) {
    echo "Root uploads type: " . (is_link(__DIR__ . '/../uploads') ? 'Symlink' : 'Physical Directory') . "\n";
    echo "Root uploads points to: " . readlink(__DIR__ . '/../uploads') . "\n";
}

echo "\nDiagnostic completed successfully.\n";
