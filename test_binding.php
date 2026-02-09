<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "Checking App\Contracts\RoomRepositoryContract: " . (interface_exists('App\Contracts\RoomRepositoryContract') ? 'EXISTS' : 'NOT FOUND') . "\n";
try {
    $repo = app('App\Contracts\RoomRepositoryContract');
    echo "Resolved App\Contracts\RoomRepositoryContract: " . get_class($repo) . "\n";
} catch (\Exception $e) {
    echo "Failed to resolve: " . $e->getMessage() . "\n";
}
