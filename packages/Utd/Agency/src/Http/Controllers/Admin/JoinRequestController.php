<?php

namespace Utd\Agency\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Encore\Admin\Layout\Content;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Utd\Agency\Entities\AgencyJoinRequest;
use Utd\Agency\Repositories\AgencyJoinRequestRepository;
use Utd\Agency\Services\AgencyService;

class JoinRequestController extends Controller
{
    protected $title = 'Join Requests';

    public function __construct(
        protected AgencyJoinRequestRepository $joinRequestRepository,
        protected AgencyService $agencyService
    ) {}

    /**
     * Index interface
     */
    public function index(Content $content)
    {
        return $content
            ->title(__('Agency Join Requests'))
            ->description(__('Pending join requests'))
            ->body($this->grid());
    }

    /**
     * Make a grid builder
     */
    protected function grid()
    {
        $grid = new Grid(new AgencyJoinRequest());

        $grid->model()->where('status', 0)->orderByDesc('id');

        $grid->column('id', __('ID'))->sortable();
        $grid->column('user.name', __('User'));
        $grid->column('user.uuid', __('UUID'));
        $grid->column('agency.name', __('Agency'));
        $grid->column('whatsapp', __('WhatsApp'));
        $grid->column('status', __('Status'))->display(function ($status) {
            $labels = [
                0 => '<span class="label label-warning">Pending</span>',
                1 => '<span class="label label-success">Accepted</span>',
                2 => '<span class="label label-danger">Rejected</span>',
            ];
            return $labels[$status] ?? $status;
        });
        $grid->column('created_at', __('Created At'))->sortable();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('agency_id', __('Agency ID'));
            $filter->equal('status', __('Status'))->select([
                0 => 'Pending',
                1 => 'Accepted',
                2 => 'Rejected',
            ]);
        });

        $grid->disableCreateButton();

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();

            // Add custom actions
            $actions->add(new \Utd\Agency\Actions\AcceptJoinRequestAction());
            $actions->add(new \Utd\Agency\Actions\RejectJoinRequestAction());
        });

        return $grid;
    }

    /**
     * Accept request
     */
    public function accept(Request $request, $id)
    {
        try {
            $joinRequest = $this->joinRequestRepository->findById($id);
            
            if (!$joinRequest) {
                return response()->json([
                    'status' => false,
                    'message' => 'Request not found',
                ], 404);
            }

            $this->agencyService->acceptRequest($joinRequest->agency_id, $joinRequest->user_id);
            $this->joinRequestRepository->accept($id);
            
            return response()->json([
                'status' => true,
                'message' => 'Request accepted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reject request
     */
    public function reject(Request $request, $id)
    {
        try {
            $this->joinRequestRepository->reject($id);
            
            return response()->json([
                'status' => true,
                'message' => 'Request rejected successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
