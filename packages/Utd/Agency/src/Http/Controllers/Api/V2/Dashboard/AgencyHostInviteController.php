<?php

namespace Utd\Agency\Http\Controllers\Api\V2\Dashboard;

use App\Helpers\Common;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;
use Utd\Agency\Contracts\AgencyHostInviteServiceInterface;
// Import external classes
use Utd\Agency\Traits\ResolvesExternalDependencies;
use Utd\Agency\Transformers\AgencyInvitationResource;

class AgencyHostInviteController extends Controller
{
    use ResolvesExternalDependencies;

    protected $agencyHostInviteService;

    public function __construct(?AgencyHostInviteServiceInterface $agencyHostInviteService = null)
    {
        // Use injected service if available, otherwise resolve from config
        $this->agencyHostInviteService = $agencyHostInviteService ?? $this->getAgencyHostInviteService();

        // If still null, throw exception
        if (! $this->agencyHostInviteService) {
            throw new RuntimeException('AgencyHostInviteService is not configured. Please configure it in agency-dependencies.php');
        }
    }

    public function invite_user_to_hostAgency(Request $request)
    {
        if (! $request->user_id2) {
            return Common::apiResponse(0, 'المستخدم مطلوب!', 423);
        }

        try {
            $this->agencyHostInviteService->inviteAgency($request);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }

        return Common::apiResponse(1, 'تم ارسال الدعوه بنجاح', [], 200);
    }

    public function agencyHostInvitation(Request $request)
    {
        try {
            $invitations = $this->agencyHostInviteService->hostInvitation($request);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }

        return Common::apiResponse(1, '', AgencyInvitationResource::collection($invitations), 200);
    }

    public function actionInvitation(Request $request)
    {
        if (! $request->invite_id || ! $request->status) {
            return Common::apiResponse(0, 'البيانات غير مكتمله', 423);
        }
        try {
            $this->agencyHostInviteService->inviteAction($request);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }

        return Common::apiResponse(1, 'تم التعديل بنجاح', [], 200);
    }
}
