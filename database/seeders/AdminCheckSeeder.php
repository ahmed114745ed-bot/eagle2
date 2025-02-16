<?php

namespace Database\Seeders;

use App\Models\Cp;
use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\SalaryTransaction\Entities\AdminCheck;
use Modules\SalaryTransaction\Entities\SalaryRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminCheckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $requests =  SalaryRequest::take(3)->orderByDesc('id')->get();
        foreach ($requests as $request) {
            AdminCheck::create(['request_id' => $request->id]);
        }
    }
}
