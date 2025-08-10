<?php
namespace App\Http\Controllers;

use App\Jobs\MigrateOldBdSalariesJob;
use App\Models\Bd;
use App\Models\BDSallary;
use App\Models\BdAgencyHostSallary;
use App\Models\BdSalary;
use App\Models\UserSallary;
use Illuminate\Support\Facades\DB;

class BdSalaryMigrationController extends Controller
{
       public function migrate()
    {
   
            MigrateOldBdSalariesJob::dispatchSync();
    
            return response()->json([
                'status' => true,
                'message' => 'done',
            ]);
        
    }
}
