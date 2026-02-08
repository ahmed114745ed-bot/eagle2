<?php

namespace Utd\Agency\Contracts;

interface AgencyServiceInterface
{
    /**
     * Find agency by ID
     */
    public function find($id);
    
    /**
     * Join user to agency
     */
    public function joinAgency($user, $request);
    
    /**
     * Get agency members
     */
    public function agencyMembers($agencyId);
    
    /**
     * Get agency target
     */
    public function agencyTarget($agencyId, $user, $request);
    
    /**
     * Get agency stars
     */
    public function stars($agencyId, $request);
    
    /**
     * Get agency heroes
     */
    public function heroes($agencyId, $request);
    
    /**
     * Show agency requests
     */
    public function showRequests($userId);
    
    /**
     * Handle request action
     */
    public function requestAction($owner, $request);
    
    /**
     * Get list options for history
     */
    public function listOption($agencyId);
    
    /**
     * Search agency history
     */
    public function historySearch($agencyId, $request);
    
    /**
     * Update agency
     */
    public function update($userId, $agencyId, $request);
    
    /**
     * Handle user request management
     */
    public function userHandlingRequest($userId, $agencyId, $type);
    
    /**
     * Get all charged agencies
     */
    public function allAgencyCharged($agencyId);
    
    /**
     * Get old agencies for user
     */
    public function gitOldAgencies($userId);
    
    /**
     * Create new agency
     */
    public function create($userId, $request);
    
    /**
     * Action on agency request (accept/reject)
     */
    public function actionRequestAgency($request);
    
    /**
     * Get all agency requests
     */
    public function allRequest();
    
    /**
     * Get history for last 30 days
     */
    public function historyLastThirtyDays($userUuid);
    
    /**
     * Get agency report
     */
    public function agencyReport($agencyId);
    
    /**
     * Leave agency
     */
    public function leaveAgency($userId, $agency);
    
    /**
     * Handle user request
     */
    public function handlingRequest($agencyId, $userId);
    
    /**
     * Kick user from agency
     */
    public function kickAgency($user, $userId);
    
    /**
     * Filter agencies
     */
    public function filter($keyword);
    
    /**
     * Get daily report
     */
    public function dailyReport($user, $month, $year, $agencyId);
    
    /**
     * Get agency data
     */
    public function dataAgency();
    
    /**
     * Get host report
     */
    public function hostReport($id);
    
    /**
     * Get host daily report
     */
    public function hostDailyReport($request);
    
    /**
     * Edit agency
     */
    public function editAgency($request);
}
