<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$bound = app()->bound('App\Contracts\RoomRepositoryContract');
echo "Is App\Contracts\RoomRepositoryContract bound? " . ($bound ? 'YES' : 'NO') . "\n";

if (!$bound) {
    echo "Bindings: \n";
    // This might be too much, but let's try to see some
}
