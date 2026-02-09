<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$interface = 'App\Contracts\RoomRepositoryContract';
$bound = app()->bound($interface);
echo "Is $interface bound? " . ($bound ? 'YES' : 'NO') . "\n";

if ($bound) {
    try {
        $repo = app($interface);
        echo "Successfully resolved to: " . get_class($repo) . "\n";
    } catch (\Exception $e) {
        echo "Failed to resolve: " . $e->getMessage() . "\n";
    }
}
