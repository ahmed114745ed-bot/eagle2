<?php
$dirs = ['app', 'routes', 'database', 'packages'];
$replacements = [
    // Models
    'App\Models\Exchange' => 'Utd\UsersWallet\Entities\Exchange',
    'App\Models\ExchangeLog' => 'Utd\UsersWallet\Entities\ExchangeLog',
    'App\Models\CoreWallet' => 'Utd\UsersWallet\Entities\CoreWallet',
    'App\Models\CoreWallets' => 'Utd\UsersWallet\Entities\CoreWallets',
    'App\Models\CoreWalletTransaction' => 'Utd\UsersWallet\Entities\CoreWalletTransaction',
    'App\Models\WalletTransaction' => 'Utd\UsersWallet\Entities\WalletTransaction',
    'App\Models\WalletTransactionBackup' => 'Utd\UsersWallet\Entities\WalletTransactionBackup',
    'App\Models\PaymentWithdrawType' => 'Utd\UsersWallet\Entities\PaymentWithdrawType',
    'App\Models\PaymentWithdrawField' => 'Utd\UsersWallet\Entities\PaymentWithdrawField',
    'App\Models\UserPaymentWithdraw' => 'Utd\UsersWallet\Entities\UserPaymentWithdraw',
    'App\Models\UserPaymentWithdrawField' => 'Utd\UsersWallet\Entities\UserPaymentWithdrawField',
    'App\Models\UserWallet' => 'Utd\UsersWallet\Entities\UserWallet',

    // Services
    'App\Tik\Services\CoreWalletsService' => 'Utd\UsersWallet\Services\CoreWalletsService',

    // Controllers
    'App\Http\Controllers\utd\ExchangeController' => 'Utd\UsersWallet\Http\Controllers\Web\ExchangeController',
    'App\Http\Controllers\utd\WithdrawController' => 'Utd\UsersWallet\Http\Controllers\Web\WithdrawController',
    'App\Http\Controllers\Api\V1\CoreWalletsController' => 'Utd\UsersWallet\Http\Controllers\Api\CoreWalletsController',

    // Some escaped versions just in case
    'App\\\\Models\\\\Exchange' => 'Utd\\\\UsersWallet\\\\Entities\\\\Exchange',
    'App\\\\Models\\\\ExchangeLog' => 'Utd\\\\UsersWallet\\\\Entities\\\\ExchangeLog',
    'App\\\\Models\\\\UserWallet' => 'Utd\\\\UsersWallet\\\\Entities\\\\UserWallet',
];

// Re-sort to replace longer strings first to avoid partial replacements!
$keys = array_keys($replacements);
usort($keys, function ($a, $b) {
    return strlen($b) - strlen($a); });

$sortedReplacements = [];
foreach ($keys as $key) {
    if (strpos($key, 'CoreWallet\\') !== false) {
        // Just avoid edge cases manually
    }
    $sortedReplacements[$key] = $replacements[$key];
}

foreach ($dirs as $dir) {
    if (!is_dir(__DIR__ . '/' . $dir))
        continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/' . $dir));
    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $changed = false;

            foreach ($sortedReplacements as $search => $replace) {
                if (strpos($content, $search) !== false) {
                    // Make sure we only replace whole words (for Exchange vs ExchangeLog)
                    // We handle this loosely via str_replace since we sorted by length.
                    $content = str_replace($search, $replace, $content);
                    $changed = true;
                }
            }
            if ($changed) {
                file_put_contents($file->getPathname(), $content);
                echo "Updated: " . $file->getPathname() . "\n";
            }
        }
    }
}
echo "Done.\n";
