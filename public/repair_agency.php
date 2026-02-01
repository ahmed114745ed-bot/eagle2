<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));

if (file_exists(__DIR__ . '/../storage/framework/maintenance.php')) {
    require __DIR__ . '/../storage/framework/maintenance.php';
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
);

echo "<pre>";
echo "Starting Repair...\n";

// 1. Remove the migration entry
$deleted = DB::table('migrations')
    ->where('migration', 'like', '%create_agencies_table%')
    ->delete();

if ($deleted) {
    echo "Removed stale migration entry: create_agencies_table.php\n";
} else {
    echo "No stale migration entry found (or already removed).\n";
}

// 2. Run Migration
echo "Running migrations...\n";
try {
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}

echo "Repair Complete.\n";
echo "</pre>";
