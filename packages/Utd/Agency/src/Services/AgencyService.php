<?php

namespace Utd\Agency\Services;

use Exception;
use Carbon\Carbon;
use Utd\Agency\Entities\Agency;
use Utd\Agency\Contracts\AgencyServiceInterface;
use Utd\Agency\Repositories\AgencyRepository;
use Utd\Agency\Repositories\AgencyJoinRequestRepository;
use Utd\Agency\Repositories\AgencyUserJobRepository;
use Utd\Agency\Repositories\AgencySalaryRepository;
use Utd\Agency\Repositories\LeaveAgencyRequestRepository;
use Utd\Agency\Repositories\UsersJoinedAgencyRepository;
use Utd\Agency\Repositories\AdditionalInfoRepository;
use Utd\Agency\Traits\ConfigurableModelsTrait;
use Illuminate\Support\Facades\DB;

class AgencyService implements AgencyServiceInterface
{
    use ConfigurableModelsTrait;

    public function __construct(
        protected AgencyRepository $agencyRepository,
        protected AgencyJoinRequestRepository $agencyJoinRequestRepository,
        protected AgencyUserJobRepository $agencyUserJobRepository,
        protected AgencySalaryRepository $agencySalaryRepository,
        protected LeaveAgencyRequestRepository $leaveAgencyRequestRepository,
        protected UsersJoinedAgencyRepository $usersJoinedAgencyRepository,
        protected AdditionalInfoRepository $additionalInfoRepository,
    ) {}

    /**
     * Get user model class from config
     */
    protected function getUserModel(): string
    {
        return $this->getModelClass('user');
    }

    /**
     * Find user by ID
     */
    protected function findUser($userId)
    {
        $userModel = $this->getUserModel();
        return $userModel::find($userId);
    }

    /**
     * Find user by UUID
     */
    protected function findUserByUuid($uuid)
    {
        $userModel = $this->getUserModel();
        return $userModel::where('uuid', $uuid)->first();
    }

    /**
     * Join agency
     */
    public function joinAgency($user, $request)
    {
        $agencyId = $request->agency_id;
        $agency = $this->agencyRepository->findById($agencyId);
        
        if (!$agency) {
            throw new Exception(__('api_responses.agency'));
        }
        
        if ($agency->status == 0) {
            throw new Exception(__('api_responses.agencyDown'));
        }
        
        if ($agency->type == 2) {
            throw new Exception(__('api_responses.shippingAgency'));
        }

        $joined = $user->agency_id;
        if ($joined) {
            throw new Exception(__('api_responses.you_are_already_under_agency'));
        }
        
        $agency_request = $this->agencyJoinRequestRepository->countByAgency($user->id, $agencyId);
        if ($agency_request > 0) {
            throw new Exception(__('api_responses.you_already_send_request_to_this_agency'));
        }

        $data = [
            'user_id' => $user->id,
            'agency_id' => $agencyId,
            'whatsapp' => $request->whatsapp,
        ];
        
        $this->agencyJoinRequestRepository->create($data);

        $requests = $this->agencyJoinRequestRepository->getByUser($user->id);

        return $requests;
    }

    /**
     * Find agency
     */
    public function find($agencyId)
    {
        $agency = $this->agencyRepository->findById($agencyId);
        
        if (!$agency) {
            throw new Exception(__('api_responses.agency'));
        }
        
        return $agency;
    }

    /**
     * Get old agencies for user
     */
    public function getOldAgencies($userId)
    {
        return $this->usersJoinedAgencyRepository->getUserHistory($userId);
    }

    /**
     * Get agency target
     */
    public function agencyTarget($userId, $user, $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month ?? Carbon::now()->month;

        return [
            'success' => true,
            'message' => 'successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'month' => $month,
                'year' => $year,
            ],
            'status' => 200,
        ];
    }

    /**
     * Get stars (top receivers)
     */
    public function stars($agencyId, $request)
    {
        $timezone = config('app.timezone', 'UTC');
        $year = $request->year ?? Carbon::now($timezone)->year;
        $month = $request->month ?? Carbon::now($timezone)->month;

        $start = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth()->utc();
        $end = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->endOfMonth()->utc();

        return [
            'start' => $start,
            'end' => $end,
            'agency_id' => $agencyId,
        ];
    }

    /**
     * Get heroes (top senders)
     */
    public function heroes($agencyId, $request)
    {
        $timezone = config('app.timezone', 'UTC');
        $year = $request->year ?? Carbon::now($timezone)->year;
        $month = $request->month ?? Carbon::now($timezone)->month;

        $start = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth()->utc();
        $end = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->endOfMonth()->utc();

        return [
            'start' => $start,
            'end' => $end,
            'agency_id' => $agencyId,
        ];
    }

    /**
     * Accept join request
     */
    public function acceptRequest($agencyId, $userId)
    {
        DB::beginTransaction();
        
        try {
            // Update user's agency
            $user = $this->findUser($userId);
            
            if (!$user) {
                throw new Exception(__('agency::messages.user_not_found') ?:
                __('api_responses.user_not_found'));
            }
            
            $user->agency_id = $agencyId;
            $user->type_user = 1;
            $user->save();
            
            // Record join
            $this->usersJoinedAgencyRepository->recordJoin($userId, $agencyId);
            
            DB::commit();
            
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject join request
     */
    public function rejectRequest($agencyId, $userId)
    {
        return true;
    }

    /**
     * Kick user from agency
     */
    public function kickFromAgency($userId)
    {
        DB::beginTransaction();
        
        try {
            $user = $this->findUser($userId);
            
            if (!$user) {
                throw new Exception(__('agency::messages.user_not_found', [], 'api_responses.user_not_found'));
            }
            
            $agencyId = $user->agency_id;
            
            // Record leave
            if ($agencyId) {
                $this->usersJoinedAgencyRepository->recordLeave($userId, $agencyId);
            }
            
            // Remove from agency
            $user->agency_id = null;
            $user->type_user = 0;
            $user->save();
            
            // Remove admin roles
            $this->agencyUserJobRepository->removeAdmin($userId, $agencyId);
            
            DB::commit();
            
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create agency
     */
    public function createAgency(array $data)
    {
        DB::beginTransaction();
        
        try {
            $agency = $this->agencyRepository->create($data);
            
            // Create additional info if provided
            if (isset($data['additional_info'])) {
                $this->additionalInfoRepository->create([
                    'agency_id' => $agency->id,
                    ...$data['additional_info'],
                ]);
            }
            
            DB::commit();
            
            return $agency;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update agency
     */
    public function updateAgency($agencyId, array $data)
    {
        return $this->agencyRepository->update($agencyId, $data);
    }

    /**
     * Delete agency
     */
    public function deleteAgency($agencyId)
    {
        DB::beginTransaction();
        
        try {
            $agency = $this->agencyRepository->findById($agencyId);
            
            if (!$agency) {
                throw new Exception(__('api_responses.agency'));
            }
            
            // Remove all members from agency
            $userModel = $this->getUserModel();
            $userModel::where('agency_id', $agencyId)->update([
                'agency_id' => null,
                'type_user' => 0,
            ]);
            
            // Delete agency
            $this->agencyRepository->delete($agencyId);
            
            DB::commit();
            
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Leave agency
     */
    public function leaveAgency($user, $agencyId)
    {
        // Check if user has pending leave request
        if ($this->leaveAgencyRequestRepository->hasPendingRequest($user->id, $agencyId)) {
            throw new Exception(__('api_responses.pending_leave_request'));
        }
        
        // Create leave request
        return $this->leaveAgencyRequestRepository->create([
            'user_id' => $user->id,
            'agency_id' => $agencyId,
            'status' => 0,
        ]);
    }

    /**
     * Get history for last thirty days
     */
    public function historyLastThirtyDays($userUuid)
    {
        $user = $this->findUserByUuid($userUuid);
        
        if (!$user) {
            return [];
        }
        
        return $this->usersJoinedAgencyRepository->getUserHistory($user->id);
    }

    /**
     * Get all join requests
     */
    public function allRequest()
    {
        return $this->agencyRepository->getByAdditionalInfo();
    }

    /**
     * Get active agencies
     */
    public function getActiveAgencies($id = null, $perPage = 20, $page = 1)
    {
        return $this->agencyRepository->getActiveAgency($id, $perPage, $page);
    }

    /**
     * Get agency report
     */
    public function getAgencyReport($id = null, $month = null, $year = null, $perPage = 20, $page = 1)
    {
        return $this->agencyRepository->report($id, $month, $year, $perPage, $page);
    }

    /**
     * Make user admin
     */
    public function makeUserAdmin($userId, $agencyId)
    {
        if ($this->agencyUserJobRepository->isAdmin($userId, $agencyId)) {
            throw new Exception(__('api_responses.already_admin'));
        }
        
        return $this->agencyUserJobRepository->makeAdmin($userId, $agencyId);
    }

    /**
     * Remove user admin
     */
    public function removeUserAdmin($userId, $agencyId)
    {
        return $this->agencyUserJobRepository->removeAdmin($userId, $agencyId);
    }

    /**
     * Get agency admins
     */
    public function getAgencyAdmins($agencyId)
    {
        return $this->agencyUserJobRepository->getAdminsByAgency($agencyId);
    }

    /**
     * Get pending leave requests
     */
    public function getPendingLeaveRequests($agencyId)
    {
        return $this->leaveAgencyRequestRepository->getPendingByAgency($agencyId);
    }

    /**
     * Approve leave request
     */
    public function approveLeaveRequest($requestId)
    {
        $request = $this->leaveAgencyRequestRepository->findById($requestId);
        
        if (!$request) {
            throw new Exception(__('api_responses.request_not_found'));
        }
        
        DB::beginTransaction();
        
        try {
            // Approve request
            $this->leaveAgencyRequestRepository->approve($requestId);
            
            // Record leave
            $this->usersJoinedAgencyRepository->recordLeave($request->user_id, $request->agency_id);
            
            // Remove from agency
            $user = $this->findUser($request->user_id);
            if ($user) {
                $user->agency_id = null;
                $user->type_user = 0;
                $user->save();
            }
            
            DB::commit();
            
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject leave request
     */
    public function rejectLeaveRequest($requestId)
    {
        return $this->leaveAgencyRequestRepository->reject($requestId);
    }

    /**
     * Get agency join requests
     */
    public function getAgencyJoinRequests($agencyId, $status = null, $paginate = true, $perPage = 10)
    {
        if ($paginate) {
            return $this->agencyJoinRequestRepository->getPaginatedByAgencyWithStatus($agencyId, $status, $perPage);
        }
        
        return $this->agencyJoinRequestRepository->getByAgencyWithStatus($agencyId, $status);
    }

    /**
     * Get pending join requests for agency
     */
    public function getPendingJoinRequests($agencyId)
    {
        return $this->agencyJoinRequestRepository->getPendingByAgency($agencyId);
    }

    /**
     * Show requests (for backward compatibility with old code)
     */
    public function showRequests($userId)
    {
        // This returns the user's own requests
        return $this->agencyJoinRequestRepository->getByUser($userId);
    }

    /**
     * Agency members
     */
    public function agencyMembers($agencyId)
    {
        if (!$agencyId) {
            throw new Exception(__('api_responses.not_in_agency'));
        }

        $userModel = $this->getUserModel();
        
        return $userModel::where('agency_id', $agencyId)
            ->with('profile')
            ->paginate(10);
    }

    /**
     * Request action (accept/reject)
     */
    public function requestAction($owner, $request)
    {
        // TODO: Implement this method based on the business logic from Tik module
        throw new Exception('Method not yet implemented. Please implement from App\\Tik\\Services\\AgencyService');
    }

    /**
     * List options for history
     */
    public function listOption($agencyId)
    {
        // TODO: Implement this method based on the business logic from Tik module
        throw new Exception('Method not yet implemented. Please implement from App\\Tik\\Services\\AgencyService');
    }

    /**
     * Search agency history
     */
    public function historySearch($agencyId, $request)
    {
        // TODO: Implement this method based on the business logic from Tik module
        throw new Exception('Method not yet implemented. Please implement from App\\Tik\\Services\\AgencyService');
    }

    /**
     * Update agency
     */
    public function update($userId, $agencyId, $request)
    {
        $agency = $this->agencyRepository->findById($agencyId);
        if (!$agency) throw new Exception(__('api_responses.agency'));

        if ($agency->app_owner_id != $userId)  throw new Exception(__('api_responses.agency_app_owner'));

        if ($request->name != null) {
            $agency->name = $request->name;
        }

        if ($request->contents != null) {
            $agency->contents = $request->contents;
        }

        if ($request->get('content') != null) {
            $agency->notice = $request->get('content');
        }

        if ($request->hasFile('img')) {
            if ($agency->img && \Storage::exists($agency->img)) {
                \Storage::delete($agency->img);
            }

            $img = $request->file('img');
            $helperClass = $this->getHelperClass('common');
            $image = $helperClass::upload('agency', $img);
            $agency->img = $image;
        }

        $agency->save();

        return $agency;
    }

    /**
     * User handling request
     */
    public function userHandlingRequest($userId, $agencyId, $type)
    {
        // TODO: Implement this method based on the business logic from Tik module
        throw new Exception('Method not yet implemented. Please implement from App\\Tik\\Services\\AgencyService');
    }

    /**
     * Get all charged agencies
     */
    public function allAgencyCharged($agencyId)
    {
        // TODO: Implement this method based on the business logic from Tik module
        throw new Exception('Method not yet implemented. Please implement from App\\Tik\\Services\\AgencyService');
    }

    /**
     * Get old agencies for user
     */
    public function gitOldAgencies($userId)
    {
        // TODO: Implement this method based on the business logic from Tik module
        throw new Exception('Method not yet implemented. Please implement from App\\Tik\\Services\\AgencyService');
    }
}

