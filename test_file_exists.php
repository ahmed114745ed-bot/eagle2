<?php
echo "GiftLogService.php exists? " . (file_exists('/media/leader/par1/Doc/GitHub/Eagle/packages/Utd/Gifts/src/Services/GiftLogService.php') ? 'YES' : 'NO') . "\n";
require 'vendor/autoload.php';
echo "Trying to require file manually...\n";
require '/media/leader/par1/Doc/GitHub/Eagle/packages/Utd/Gifts/src/Services/GiftLogService.php';
echo "Class exists after manual require? " . (class_exists('Utd\Gifts\Services\GiftLogService') ? 'YES' : 'NO') . "\n";
