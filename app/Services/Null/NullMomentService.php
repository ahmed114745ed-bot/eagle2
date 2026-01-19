<?php
namespace App\Services\Null;

use App\Contracts\MomentContract;

class NullMomentService implements MomentContract
{
    public function getMomentsByType($type, $userId, $page, $currentUser)
    {
        return collect();
    }

    public function getMoment($id, $userId)
    {
        // TODO: Implement getMoment() method.
    }

    public function createMoment($contacts, $request)
    {
        // TODO: Implement createMoment() method.
    }

    public function deleteMomentById($id)
    {
        // TODO: Implement deleteMomentById() method.
    }

    public function deleteMomentAndReport($momentId, $reportId)
    {
        // TODO: Implement deleteMomentAndReport() method.
    }
}
