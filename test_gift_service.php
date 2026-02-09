<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "Checking GiftLogService...\n";
if (class_exists('Utd\Gifts\Services\GiftLogService')) {
    echo "Class Utd\Gifts\Services\GiftLogService found.\n";
    try {
        $service = app('Utd\Gifts\Services\GiftLogService');
        echo "Successfully resolved GiftLogService from container.\n";
    } catch (\Exception $e) {
        echo "Failed to resolve GiftLogService: " . $e->getMessage() . "\n";
    }
} else {
    echo "Class Utd\Gifts\Services\GiftLogService NOT found.\n";
}
