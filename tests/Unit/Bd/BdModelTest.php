<?php

namespace Tests\Unit\Bd;

use Tests\TestCase;
use Utd\Bd\Entities\Bd;
use Utd\Bd\Entities\BdSalary;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class BdModelTest extends TestCase
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

    public function test_bd_uses_admin_users_table(): void
    {
        $bd = new Bd();
        $this->assertEquals('admin_users', $bd->getTable());
    }

    public function test_bd_has_type_bd_by_default(): void
    {
        $bd = new Bd();
        $this->assertEquals('bd', $bd->type);
    }

    public function test_bd_global_scope_filters_by_type(): void
    {
        \DB::table('admin_users')->insert([
            ['username' => 'admin_test_' . uniqid(), 'name' => 'Admin', 'password' => bcrypt('x'), 'type' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['username' => 'bd_test_' . uniqid(), 'name' => 'BD User', 'password' => bcrypt('x'), 'type' => 'bd', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $bds = Bd::where('name', 'BD User')->get();
        $this->assertTrue($bds->count() >= 1);
        $bds->each(fn($bd) => $this->assertEquals('bd', $bd->type));
    }

    public function test_total_salary_attribute(): void
    {
        $bdId = \DB::table('admin_users')->insertGetId([
            'username' => 'bdtest_' . uniqid(),
            'name'     => 'Test BD',
            'password' => bcrypt('x'),
            'type'     => 'bd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        BdSalary::create(['bd_id' => $bdId, 'salary' => 1000, 'cut_amount' => 100, 'month' => 1, 'year' => 2026]);
        BdSalary::create(['bd_id' => $bdId, 'salary' => 2000, 'cut_amount' => 200, 'month' => 2, 'year' => 2026]);

        $bd = Bd::find($bdId);
        $this->assertEquals(3000, $bd->total_salary);
    }

    public function test_total_cut_attribute(): void
    {
        $bdId = \DB::table('admin_users')->insertGetId([
            'username' => 'bdtest_' . uniqid(),
            'name'     => 'Test BD',
            'password' => bcrypt('x'),
            'type'     => 'bd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        BdSalary::create(['bd_id' => $bdId, 'salary' => 1000, 'cut_amount' => 100, 'month' => 1, 'year' => 2026]);
        BdSalary::create(['bd_id' => $bdId, 'salary' => 2000, 'cut_amount' => 300, 'month' => 2, 'year' => 2026]);

        $bd = Bd::find($bdId);
        $this->assertEquals(400, $bd->total_cut);
    }

    public function test_net_salary_attribute(): void
    {
        $bdId = \DB::table('admin_users')->insertGetId([
            'username' => 'bdtest_' . uniqid(),
            'name'     => 'Test BD',
            'password' => bcrypt('x'),
            'type'     => 'bd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        BdSalary::create(['bd_id' => $bdId, 'salary' => 1500, 'cut_amount' => 200, 'month' => 1, 'year' => 2026]);
        BdSalary::create(['bd_id' => $bdId, 'salary' => 2500, 'cut_amount' => 300, 'month' => 2, 'year' => 2026]);

        $bd = Bd::find($bdId);
        $this->assertEquals(3500, $bd->net_sallary);
    }

    public function test_increment_cut_amount(): void
    {
        $bdId = \DB::table('admin_users')->insertGetId([
            'username' => 'bdtest_' . uniqid(),
            'name'     => 'Test BD',
            'password' => bcrypt('x'),
            'type'     => 'bd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        BdSalary::create(['bd_id' => $bdId, 'salary' => 1000, 'cut_amount' => 100, 'month' => 5, 'year' => 2026]);

        $bd = Bd::find($bdId);
        $result = $bd->incrementCutAmountInBdSallary(50);

        $this->assertTrue($result);

        $salary = BdSalary::where('bd_id', $bdId)->latest()->first();
        $this->assertEquals(150, $salary->cut_amount);
    }

    public function test_increment_cut_amount_returns_false_when_no_salary(): void
    {
        $bdId = \DB::table('admin_users')->insertGetId([
            'username' => 'bdtest_' . uniqid(),
            'name'     => 'Test BD',
            'password' => bcrypt('x'),
            'type'     => 'bd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $bd = Bd::find($bdId);
        $result = $bd->incrementCutAmountInBdSallary(50);

        $this->assertFalse($result);
    }

    public function test_increment_cut_amount_does_not_go_below_zero(): void
    {
        $bdId = \DB::table('admin_users')->insertGetId([
            'username' => 'bdtest_' . uniqid(),
            'name'     => 'Test BD',
            'password' => bcrypt('x'),
            'type'     => 'bd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        BdSalary::create(['bd_id' => $bdId, 'salary' => 1000, 'cut_amount' => 30, 'month' => 5, 'year' => 2026]);

        $bd = Bd::find($bdId);
        $bd->incrementCutAmountInBdSallary(-100);

        $salary = BdSalary::where('bd_id', $bdId)->first();
        $this->assertEquals(0, $salary->cut_amount);
    }

    public function test_bd_salaries_relation(): void
    {
        $bdId = \DB::table('admin_users')->insertGetId([
            'username' => 'bdtest_' . uniqid(),
            'name'     => 'Test BD',
            'password' => bcrypt('x'),
            'type'     => 'bd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        BdSalary::create(['bd_id' => $bdId, 'salary' => 500, 'cut_amount' => 0, 'month' => 1, 'year' => 2026]);
        BdSalary::create(['bd_id' => $bdId, 'salary' => 700, 'cut_amount' => 0, 'month' => 2, 'year' => 2026]);

        $bd = Bd::find($bdId);
        $this->assertEquals(2, $bd->bdSalaries()->count());
    }
}
