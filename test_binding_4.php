<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->register(\Utd\Room\RoomServiceProvider::class);

echo "Is RoomServiceProvider loaded? " . ($app->providerIsLoaded('Utd\Room\RoomServiceProvider') ? 'YES' : 'NO') . "\n";
$bound = app()->bound('App\Contracts\RoomRepositoryContract');
echo "Is App\Contracts\RoomRepositoryContract bound? " . ($bound ? 'YES' : 'NO') . "\n";
if ($bound) {
    $repo = app('App\Contracts\RoomRepositoryContract');
    echo "Resolved to: " . get_class($repo) . "\n";
}
