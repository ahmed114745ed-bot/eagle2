<?php

namespace App\Traits\Salaries;

use App\Helpers\Common;

trait UserSalaryTrait
{
    function checkStoredProcedureExists($procedureName)
    {
        $databaseName = config('database.connections.mysql.database'); // Get the database name from the environment file

        $result = \DB::select(
            'SELECT COUNT(*) as count
        FROM information_schema.ROUTINES
        WHERE ROUTINE_TYPE = ?
        AND ROUTINE_SCHEMA = ?
        AND ROUTINE_NAME = ?',
            ['PROCEDURE', $databaseName, $procedureName]
        );

        return $result[0]->count > 0;
    }

    /**
     * @param int $month
     * @param int $year
     * @param bool $allTargetOrNothing
     * @return void
     */
    public function callProcedure(int $month, int $year, bool $allTargetOrNothing): void
    {
        \DB::statement('CALL _SP_calculate_users_salaries(:month, :year, :allTargetOrNothing)', [
            'month' => $month,
            'year' => $year,
            'allTargetOrNothing' => $allTargetOrNothing,
        ]);
    }

    /**
     * @return void
     */
    public function updateUserSalary($month = null, $year = null): void
    {
        $procedureName = '_SP_calculate_users_salaries';

        $checkStoredProcedureExists = $this->checkStoredProcedureExists($procedureName);
        $now = now();
        $month = $month ?? $now->month;
        $year = $year ?? $now->year;
        $allTargetOrNothing = Common::getConfig('all_target_or_nothing') == 'true';

//        DB::statement('SET SESSION sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"');

        if ($checkStoredProcedureExists) {

            $this->callProcedure($month, $year, $allTargetOrNothing);
        } else {
            $file = database_path('user-salaries.sql');
            $query = file_get_contents($file);
            \DB::statement($query);

            $this->callProcedure($month, $year, $allTargetOrNothing);

        }
    }
}
