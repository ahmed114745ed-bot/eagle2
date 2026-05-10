<?php

namespace Tests\Feature\Bd;

use Tests\TestCase;
use Utd\Bd\Entities\Bd;
use Utd\Bd\Entities\BdSalary;
use Utd\Bd\Entities\BdAgencyHostSallary;
use Utd\Bd\Services\BdSalaryService;
use Utd\Bd\Services\BdAgencyHostSallaryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class BdSalaryFlowTest extends TestCase
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

    public function test_full_salary_flow_multiple_hosts_accumulate(): void
    {
        $bdId = $this->createBd();

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 101,
            'agency_id'  => 1,
            'amount'     => 200,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 102,
            'agency_id'  => 2,
            'amount'     => 300,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 103,
            'agency_id'  => 3,
            'amount'     => 500,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        $bdSalary = BdSalary::where('bd_id', $bdId)->where('month', 5)->first();
        $this->assertNotNull($bdSalary);
        $this->assertEquals(1000, $bdSalary->salary);

        $lines = BdAgencyHostSallary::where('bd_id', $bdId)->count();
        $this->assertEquals(3, $lines);
    }

    public function test_salary_update_flow_with_increasing_amounts(): void
    {
        $bdId = $this->createBd();

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 101,
            'agency_id'  => 1,
            'amount'     => 100,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 101,
            'agency_id'  => 1,
            'amount'     => 400,
            'oldDbValue' => 100,
            'month'      => 5,
            'year'       => 2026,
        ]);

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 101,
            'agency_id'  => 1,
            'amount'     => 1000,
            'oldDbValue' => 400,
            'month'      => 5,
            'year'       => 2026,
        ]);

        $lines = BdAgencyHostSallary::where('bd_id', $bdId)->orderBy('id')->get();
        $this->assertEquals(3, $lines->count());
        $this->assertEquals(100, $lines[0]->amount);
        $this->assertEquals(300, $lines[1]->amount);
        $this->assertEquals(600, $lines[2]->amount);

        $bdSalary = BdSalary::where('bd_id', $bdId)->where('month', 5)->first();
        $this->assertEquals(1000, $bdSalary->salary);
    }

    public function test_bd_salary_direct_service_then_host_service(): void
    {
        $bdId = $this->createBd();

        BdSalaryService::storeOrUpdate([
            'bd_id' => $bdId,
            'month' => 5,
            'year'  => 2026,
            'salary' => 500,
            'deducted_salary' => 50,
        ]);

        $salary = BdSalary::where('bd_id', $bdId)->first();
        $this->assertEquals(500, $salary->salary);
        $this->assertEquals(50, $salary->cut_amount);

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 101,
            'agency_id'  => 1,
            'amount'     => 800,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        $salary->refresh();
        $this->assertEquals(800, $salary->salary);
    }

    public function test_separate_months_do_not_interfere(): void
    {
        $bdId = $this->createBd();

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 101,
            'agency_id'  => 1,
            'amount'     => 500,
            'oldDbValue' => 0,
            'month'      => 4,
            'year'       => 2026,
        ]);

        BdAgencyHostSallaryService::storeOrUpdate([
            'bd_id'      => $bdId,
            'user_id'    => 101,
            'agency_id'  => 1,
            'amount'     => 300,
            'oldDbValue' => 0,
            'month'      => 5,
            'year'       => 2026,
        ]);

        $salaryApril = BdSalary::where('bd_id', $bdId)->where('month', 4)->first();
        $salaryMay   = BdSalary::where('bd_id', $bdId)->where('month', 5)->first();

        $this->assertEquals(500, $salaryApril->salary);
        $this->assertEquals(300, $salaryMay->salary);
    }

    public function test_bd_model_attributes_reflect_salary_data(): void
    {
        $bdId = $this->createBd();

        BdSalary::create(['bd_id' => $bdId, 'salary' => 2000, 'cut_amount' => 300, 'month' => 1, 'year' => 2026]);
        BdSalary::create(['bd_id' => $bdId, 'salary' => 3000, 'cut_amount' => 500, 'month' => 2, 'year' => 2026]);

        $bd = Bd::find($bdId);

        $this->assertEquals(5000, $bd->total_salary);
        $this->assertEquals(800, $bd->total_cut);
        $this->assertEquals(4200, $bd->net_sallary);
        $this->assertEquals(4200, $bd->bd_salary);
    }

    private function createBd(): int
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
