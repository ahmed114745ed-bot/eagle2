<?php

use App\Http\Controllers\Admin\FairLuckWalletController;
use Illuminate\Support\Facades\Route;

// Admin routes for FairLuck Wallet Management
Route::prefix('admin/fairluck/wallets')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [FairLuckWalletController::class, 'index'])->name('admin.fairluck.wallets.index');
    Route::post('/update-balance', [FairLuckWalletController::class, 'updateBalance'])->name('admin.fairluck.wallets.update-balance');
    Route::post('/transfer', [FairLuckWalletController::class, 'transfer'])->name('admin.fairluck.wallets.transfer');
    Route::post('/rebalance', [FairLuckWalletController::class, 'rebalance'])->name('admin.fairluck.wallets.rebalance');
    Route::get('/validate', [FairLuckWalletController::class, 'validateIntegrity'])->name('admin.fairluck.wallets.validate');
    Route::get('/stats', [FairLuckWalletController::class, 'stats'])->name('admin.fairluck.wallets.stats');
});

// API routes (optional - for external monitoring)
Route::prefix('api/fairluck/wallets')->middleware(['auth:api'])->group(function () {
    Route::get('/balances', [FairLuckWalletController::class, 'index']);
    Route::get('/stats', [FairLuckWalletController::class, 'stats']);
});