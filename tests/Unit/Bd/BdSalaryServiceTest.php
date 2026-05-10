<?php

namespace Tests\Unit\Bd;

use Tests\TestCase;
use Utd\Bd\Entities\BdSalary;
use Utd\Bd\Services\BdSalaryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class BdSalaryServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('bd_salaries')) {
            Schema::create('bd_salaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bd_id');
                $table->decimal('salary', 12, 2)->default(0);
                $table->decimal('cut_amount', 12, 2)->default(0);
                $table->bigInteger('month');
                $table->bigInteger('year');
                $table->timestamps();
            });
        }
    }

    public function test_store_creates_new_salary_record(): void
    {
        $bdId = 888801;

        BdSalaryService::storeOrUpdate([
            'bd_id' => $bdId,
            'month' => 5,
            'year'  => 2026,
            'salary' => 1500,
            'deducted_salary' => 200,
        ]);

        $this->assertDatabaseHas('bd_salaries', [
            'bd_id'      => $bdId,
            'month'      => 5,
            'year'       => 2026,
            'salary'     => 1500,
            'cut_amount' => 200,
        ]);
    }

    public function test_store_updates_existing_salary_record(): void
    {
        $bdId = 999900 + rand(1, 99);

        BdSalary::create([
            'bd_id'      => $bdId,
            'salary'     => 1000,
            'cut_amount' => 100,
            'month'      => 5,
            'year'       => 2026,
        ]);

        BdSalaryService::storeOrUpdate([
            'bd_id' => $bdId,
            'month' => 5,
            'year'  => 2026,
            'salary' => 2000,
            'deducted_salary' => 300,
        ]);

        $count = BdSalary::where('bd_id', $bdId)->where('month', 5)->where('year', 2026)->count();
        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('bd_salaries', [
            'bd_id'      => $bdId,
            'salary'     => 2000,
            'cut_amount' => 300,
        ]);
    }

    public function test_store_defaults_salary_to_zero_when_missing(): void
    {
        $bdId = 888802;

        BdSalaryService::storeOrUpdate([
            'bd_id' => $bdId,
            'month' => 1,
            'year'  => 2026,
        ]);

        $this->assertDatabaseHas('bd_salaries', [
            'bd_id'      => $bdId,
            'salary'     => 0,
            'cut_amount' => 0,
        ]);
    }

    public function test_store_creates_separate_records_for_different_months(): void
    {
        $bdId = 888803;

        BdSalaryService::storeOrUpdate([
            'bd_id' => $bdId,
            'month' => 3,
            'year'  => 2026,
            'salary' => 1000,
        ]);

        BdSalaryService::storeOrUpdate([
            'bd_id' => $bdId,
            'month' => 4,
            'year'  => 2026,
            'salary' => 1500,
        ]);

        $count = BdSalary::where('bd_id', $bdId)->count();
        $this->assertEquals(2, $count);
    }

    public function test_store_creates_separate_records_for_different_bds(): void
    {
        $bdId1 = 888804;
        $bdId2 = 888805;

        BdSalaryService::storeOrUpdate([
            'bd_id' => $bdId1,
            'month' => 5,
            'year'  => 2026,
            'salary' => 1000,
        ]);

        BdSalaryService::storeOrUpdate([
            'bd_id' => $bdId2,
            'month' => 5,
            'year'  => 2026,
            'salary' => 2000,
        ]);

        $count1 = BdSalary::where('bd_id', $bdId1)->count();
        $count2 = BdSalary::where('bd_id', $bdId2)->count();
        $this->assertEquals(1, $count1);
        $this->assertEquals(1, $count2);
    }

    public function test_store_returns_bd_salary_instance(): void
    {
        $bdId = 888806;

        $result = BdSalaryService::storeOrUpdate([
            'bd_id' => $bdId,
            'month' => 5,
            'year'  => 2026,
            'salary' => 500,
        ]);

        $this->assertInstanceOf(BdSalary::class, $result);
        $this->assertEquals(500, $result->salary);
    }
}
