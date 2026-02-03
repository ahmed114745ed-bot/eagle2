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
}
