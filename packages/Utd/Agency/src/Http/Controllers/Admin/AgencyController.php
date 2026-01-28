<?php

namespace Utd\Agency\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Utd\Agency\Entities\Agency;
use Utd\Agency\Services\AgencyService;

class AgencyController extends Controller
{
    protected $title = 'Agencies';

    public function __construct(
        protected AgencyService $agencyService
    ) {}

    /**
     * Index interface
     */
    public function index(Content $content)
    {
        return $content
            ->title(__('Agencies'))
            ->description(__('List of Agencies'))
            ->body($this->grid());
    }

    /**
     * Show interface
     */
    public function show($id, Content $content)
    {
        return $content
            ->title(__('Agency Details'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title(__('Edit Agency'))
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface
     */
    public function create(Content $content)
    {
        return $content
            ->title(__('Create Agency'))
            ->body($this->form());
    }

    /**
     * Make a grid builder
     */
    protected function grid()
    {
        $grid = new Grid(new Agency());

        $grid->model()->orderByDesc('id');

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'));
        $grid->column('owner.name', __('Owner'));
        $grid->column('phone', __('Phone'));
        $grid->column('status', __('Status'))->display(function ($status) {
            return $status == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-warning">Inactive</span>';
        });
        $grid->column('mempers_count', __('Members'))->display(function () {
            return $this->mempers()->count();
        });
        $grid->column('created_at', __('Created At'))->sortable();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('name', __('Name'));
            $filter->equal('status', __('Status'))->select([
                0 => 'Inactive',
                1 => 'Active',
            ]);
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a show builder
     */
    protected function detail($id)
    {
        $show = new Show(Agency::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('Name'));
        $show->field('notice', __('Notice'));
        $show->field('phone', __('Phone'));
        $show->field('status', __('Status'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder
     */
    protected function form()
    {
        $form = new Form(new Agency());

        $form->text('name', __('Name'))->rules('required');
        $form->textarea('notice', __('Notice'));
        $form->text('phone', __('Phone'));
        $form->image('img', __('Image'));
        $form->switch('status', __('Status'))->default(1);

        return $form;
    }

    /**
     * Store agency
     */
    public function store(Request $request)
    {
        try {
            $agency = $this->agencyService->createAgency($request->all());
            
            return redirect()->route('admin.agencies.index')
                ->with('success', 'Agency created successfully');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update agency
     */
    public function update(Request $request, $id)
    {
        try {
            $agency = $this->agencyService->updateAgency($id, $request->all());
            
            return redirect()->route('admin.agencies.index')
                ->with('success', 'Agency updated successfully');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete agency
     */
    public function destroy($id)
    {
        try {
            $this->agencyService->deleteAgency($id);
            
            return response()->json([
                'status' => true,
                'message' => 'Agency deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Accept join request
     */
    public function acceptJoin(Request $request, $id)
    {
        try {
            $this->agencyService->acceptRequest($id, $request->user_id);
            
            return response()->json([
                'status' => true,
                'message' => 'Join request accepted',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reject join request
     */
    public function rejectJoin(Request $request, $id)
    {
        try {
            $this->agencyService->rejectRequest($id, $request->user_id);
            
            return response()->json([
                'status' => true,
                'message' => 'Join request rejected',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Kick user from agency
     */
    public function kick(Request $request, $id)
    {
        try {
            $this->agencyService->kickFromAgency($request->user_id);
            
            return response()->json([
                'status' => true,
                'message' => 'User kicked from agency',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get agency profile
     */
    public function profile($id, Content $content)
    {
        $agency = $this->agencyService->find($id);
        
        return $content
            ->title(__('Agency Profile'))
            ->body(view('agency::admin.profile', compact('agency')));
    }
}
