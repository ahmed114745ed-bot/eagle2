<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Loaded providers:\n";
foreach (array_keys($app->getLoadedProviders()) as $provider) {
    if (str_contains($provider, 'Utd')) {
        echo " - $provider\n";
    }
}
