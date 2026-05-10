<?php

namespace Utd\Bd\Http\Controllers;

use Utd\Bd\Jobs\MigrateOldBdSalariesJob;
use Illuminate\Routing\Controller;

class BdSalaryMigrationController extends Controller
{
    public function migrate()
    {
        MigrateOldBdSalariesJob::dispatch()->onQueue('migrations_bd_salaries');

        return response()->json([
            'status' => true,
            'message' => 'done',
        ]);
    }
}
