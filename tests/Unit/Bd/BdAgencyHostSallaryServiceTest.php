<?php

namespace Tests\Unit\Bd;

use Tests\TestCase;
use Utd\Bd\Entities\Bd;
use Utd\Bd\Entities\BdSalary;
use Utd\Bd\Entities\BdAgencyHostSallary;
use Utd\Bd\Services\BdAgencyHostSallaryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class BdAgencyHostSallaryServiceTest extends TestCase
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

        if (!Schema::hasTable('bd_agency_host_sallaries')) {
            Schema::create('bd_agency_host_sallaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bd_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('agency_id');
                $table->decimal('amount', 20, 4)->default(0);
                $table->decimal('salary', 12, 4)->nullable();
                $table->bigInteger('month');
                $table->bigInteger('year');
                $table->unsignedBigInteger('bd_user_id')->nullable();
                $table->timestamps();
            });
        }

    }

    public function test_store_or_update_creates_salary_line_and_bd_salary(): void
    {
        $bdId = $this->createBdRecord();

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 100,
            'agency_id'  => 10,
            'amount'     => 500,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        $this->assertDatabaseHas('bd_agency_host_sallaries', [
            'bd_id'     => $bdId,
            'user_id'   => 100,
            'agency_id' => 10,
            'amount'    => 500,
        ]);

        $this->assertDatabaseHas('bd_salaries', [
            'bd_id' => $bdId,
            'month' => 5,
            'year'  => 2026,
        ]);
    }

    public function test_store_accumulates_salary_from_multiple_agencies(): void
    {
        $bdId = $this->createBdRecord();

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 100,
            'agency_id'  => 10,
            'amount'     => 300,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 200,
            'agency_id'  => 20,
            'amount'     => 700,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        $bdSalary = BdSalary::where('bd_id', $bdId)
            ->where('month', 5)
            ->where('year', 2026)
            ->first();

        $this->assertEquals(1000, $bdSalary->salary);
    }

    public function test_should_skip_insert_when_duplicate_data(): void
    {
        $bdId = $this->createBdRecord();

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 100,
            'agency_id'  => 10,
            'amount'     => 500,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        $countBefore = BdAgencyHostSallary::count();

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 100,
            'agency_id'  => 10,
            'amount'     => 500,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        $this->assertEquals($countBefore, BdAgencyHostSallary::count());
    }

    public function test_store_calculates_difference_from_old_value(): void
    {
        $bdId = $this->createBdRecord();

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 100,
            'agency_id'  => 10,
            'amount'     => 300,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 100,
            'agency_id'  => 10,
            'amount'     => 800,
            'oldDbValue' => 300,
            'month'      => 5,
            'year'       => 2026,
        ]);

        $lines = BdAgencyHostSallary::where('bd_id', $bdId)->get();

        $this->assertEquals(2, $lines->count());
        $this->assertEquals(300, $lines->first()->amount);
        $this->assertEquals(500, $lines->last()->amount);
    }

    public function test_bd_salary_updates_total_after_new_line(): void
    {
        $bdId = $this->createBdRecord();

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 100,
            'agency_id'  => 10,
            'amount'     => 1000,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        $salary = BdSalary::where('bd_id', $bdId)->where('month', 5)->first();
        $this->assertEquals(1000, $salary->salary);

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 100,
            'agency_id'  => 10,
            'amount'     => 1500,
            'oldDbValue' => 1000,
            'month'      => 5,
            'year'       => 2026,
        ]);

        $salary->refresh();
        $this->assertEquals(1500, $salary->salary);
    }

    public function test_store_with_zero_amount(): void
    {
        $bdId = $this->createBdRecord();

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 100,
            'agency_id'  => 10,
            'amount'     => 0,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        $this->assertDatabaseHas('bd_agency_host_sallaries', [
            'bd_id'  => $bdId,
            'amount' => 0,
        ]);
    }

    private function createBdRecord(): int
    {
        return \DB::table('admin_users')->insertGetId([
            'username'   => 'testbd_' . uniqid(),
            'name'       => 'Test BD',
            'password'   => bcrypt('password'),
            'type'       => 'bd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
