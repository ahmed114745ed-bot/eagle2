<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "Is RoomServiceProvider loaded? " . ($app->providerIsLoaded('Utd\Room\RoomServiceProvider') ? 'YES' : 'NO') . "\n";
$bound = app()->bound('App\Contracts\RoomRepositoryContract');
echo "Is App\Contracts\RoomRepositoryContract bound? " . ($bound ? 'YES' : 'NO') . "\n";
