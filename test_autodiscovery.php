<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
echo "Loaded providers:\n";
foreach (array_keys($app->getLoadedProviders()) as $provider) {
    if (str_contains($provider, 'Utd')) {
        echo " - $provider\n";
    }
}
