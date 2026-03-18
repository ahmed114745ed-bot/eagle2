<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ChargeCalculationService;
use Exception;

class ChargeCalculationServiceTest extends TestCase
{
    public function test_calculate_with_usd()
    {
        $amount = 10; 
        $effectiveRate = 12000;
        $baseRate = 10000;

        $result = ChargeCalculationService::calculate($amount, 'usd', $effectiveRate, $baseRate);

        $this->assertEquals(10.0, $result['base_usd']);
        $this->assertEquals(120000.0, $result['total_coins']);
        $this->assertEquals(12000.0, $result['applied_coin_rate']);
        $this->assertEquals(100000.0, $result['base_coins']);
        $this->assertEquals(20000.0, $result['bonus_coins']);
        $this->assertEquals(10.0, $result['profit_usd']);
        $this->assertEquals(100000.0, $result['profit_coins']);
    }

    public function test_calculate_with_coins()
    {
        $amount = 120000; // 120,000 coins
        $effectiveRate = 12000;
        $baseRate = 10000;

        $result = ChargeCalculationService::calculate($amount, 'coins', $effectiveRate, $baseRate);

        $this->assertEquals(10.0, $result['base_usd']);
        $this->assertEquals(120000.0, $result['total_coins']);
        $this->assertEquals(12000.0, $result['applied_coin_rate']);
        $this->assertEquals(100000.0, $result['base_coins']);
        $this->assertEquals(20000.0, $result['bonus_coins']);
        $this->assertEquals(10.0, $result['profit_usd']);
        $this->assertEquals(100000.0, $result['profit_coins']);
    }

    public function test_calculate_with_no_base_rate_provided()
    {
        $amount = 10;
        $effectiveRate = 10000;

        $result = ChargeCalculationService::calculate($amount, 'usd', $effectiveRate);

        $this->assertEquals(10.0, $result['base_usd']);
        $this->assertEquals(100000.0, $result['total_coins']);
        $this->assertEquals(10000.0, $result['applied_coin_rate']);
        $this->assertEquals(100000.0, $result['base_coins']);
        $this->assertEquals(0.0, $result['bonus_coins']);
    }

    public function test_calculate_throws_exception_on_invalid_amount()
    {
        $this->expectException(Exception::class);
        ChargeCalculationService::calculate(0, 'usd', 10000);
    }
}
