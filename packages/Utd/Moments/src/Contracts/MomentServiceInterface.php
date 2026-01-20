<?php

interface MomentServiceInterface
{
    public function getMomentsByType($type, $userId, $page, $currentUser);
    public function getMoment($id, $userId);
    public function createMoment($contacts, $request);
    public function deleteMomentById($id);
    public function deleteMomentAndReport($momentId, $reportId);
}
