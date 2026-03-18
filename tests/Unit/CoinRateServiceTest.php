<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CoinRateService;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class CoinRateServiceTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function setSetting($key, $value)
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public function test_get_system_mode_defaults_to_unified()
    {
        $this->assertEquals('unified', CoinRateService::getSystemMode());
    }

    public function test_get_app_base_rate_in_unified_mode()
    {
        $this->setSetting('app_coin_rate', 10000);
        $this->assertEquals(10000, CoinRateService::getAppBaseRate());
    }

    public function test_get_app_base_rate_in_legacy_mode()
    {
        $this->setSetting('coin_rate_system_mode', 'legacy');
        $this->setSetting('shipping_coins', 9000);
        
        $this->assertEquals(9000, CoinRateService::getAppBaseRate());
    }

    public function test_get_user_transfer_rate_enabled()
    {
        $this->setSetting('app_coin_rate', 10000);
        $this->setSetting('user_transfer_rate_enabled', 1);
        $this->setSetting('user_transfer_coin_rate', 11000);

        $this->assertEquals(11000, CoinRateService::getUserTransferRate());
    }

    public function test_get_user_transfer_rate_disabled_falls_back_to_app_base_rate()
    {
        $this->setSetting('app_coin_rate', 10000);
        $this->setSetting('user_transfer_rate_enabled', 0);

        $this->assertEquals(10000, CoinRateService::getUserTransferRate());
    }

    public function test_get_effective_rate_unified()
    {
        $this->setSetting('app_coin_rate', 10000);
        $this->assertEquals(10000, CoinRateService::getEffectiveRate());
    }
}
