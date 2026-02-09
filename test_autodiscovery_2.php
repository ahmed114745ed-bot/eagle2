<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "Loaded providers:\n";
foreach (array_keys($app->getLoadedProviders()) as $provider) {
    if (str_contains($provider, 'Utd')) {
        echo " - $provider\n";
    }
}
