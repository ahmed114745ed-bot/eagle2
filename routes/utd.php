<?php

use App\Admin\Controllers\SettingController;

use App\Admin\Controllers\ZegoFeatureController;

Route::middleware([])->group(function () {

  Route::post('zego-action', [ZegoFeatureController::class, 'zegoKey']);
//
  Route::post('/update-room-cup', [SettingController::class, 'updateRoomCup']);
  Route::post('/update-room-boom', [SettingController::class, 'updateRoomBoom']);
  Route::post('/update-remaining-diamonds', [SettingController::class, 'updateRemainingDiamonds']);  
  Route::post('/update-host-level', [SettingController::class, 'updateHostLevel']);
  Route::post('/update-pk-live', [SettingController::class, 'updatePkLive']);
  Route::post('/update-lucky-gifts', [SettingController::class, 'updateLuckyGifts']);
  Route::post('/update-is-theme-enabled', [SettingController::class, 'updateIsThemeEnabled']);
  Route::post('/update-room-mode', [SettingController::class, 'updateRoomMode']);
  Route::post('/update-charisma-format', [SettingController::class, 'updateCharismaFormat']);
//
  Route::post('/update-charisma-badge', [SettingController::class, 'updateCharismaBadge']);

});
