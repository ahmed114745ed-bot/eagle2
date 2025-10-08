<?php

namespace App\Admin\Controllers;

use App\Admin\Services\SuperAdminService;
use App\Models\HomeCarouselDisplay;
use App\Models\SuperadminBannerRequest;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\helper\SuperAdminHelper;
use Encore\Admin\Layout\Content;


class SuperadminBannerRequestController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SuperadminBannerRequest';


    public function __construct(SuperAdminService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Content $content)
    {
        return $content
            ->title(__('SuperadminBannerRequest'))
            ->body($this->grid());
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SuperadminBannerRequest());
        $grid->model()->with(['superAdmin','homeCarousel:home_carousel_id.img'])->latest();
        $grid->column('id', __('ID'));
  
        $userService = $this->userService;
    
        // Super Admin
        $grid->column('user_id', __('Super Admin'))->display(function () use ($userService) {
            return $userService->adminUserAvatar($this->superAdmin ?? null, withoutLevels: true);
        });
    
        // Banner Image
        $grid->column('homeCarousel.img', __('img'))->image('', 235, 77);

    
        $grid->column('coins_deducted', __('Coins Deducted'));
        $grid->column('status', __('Status'))->display(function ($status) {
            switch ($status) {
                case 'pending': return '<span class="text-warning">'. __('Pending') .'</span>';
                case 'approved': return '<span class="text-success">'. __('Approved') .'</span>';
                case 'rejected': return '<span class="text-danger">'. __('Rejected') .'</span>';
                default: return $status;
            }
        });
        
        $grid->column('notes', __('Type'))->display(function ($value) {
            if ($value === 'display_discover') {
                return __('Display Discover');
            } elseif ($value === 'display_home_top') {
                return __('Display Home Top');
            } elseif ($value === 'display_home_middle') {
                return __('Display Home Middle');
            } elseif ($value === 'display_live') {
                return __('Display Live');
            } 
            else {
                return $value; 
            }
        });
        $grid->column('hours', __('hours'));
        $grid->column('created_at', __('Created At'))
        ->display(function ($createdAt) {
            return \Carbon\Carbon::parse($createdAt)->format('d/m/Y H:i');
        });
        
        // Actions
        $grid->column('actions', __('Actions'))->display(function () {
            $approveUrl = route('admin.superadmin-banner.approve', $this->id);
            $rejectUrl  = route('admin.superadmin-banner.reject', $this->id);
    
            if ($this->status === 'rejected') {
                return '<span class="text-danger">' . __('Rejected') . '</span>';
            }
    
            if ($this->status === 'approved') {
                return '<span class="text-success">' . __('Approved') . '</span>';
            }
            $approveText = __('Approved');
            $rejectText  = __('Reject');
            
            return <<<HTML
            <button class="btn btn-success btn-sm approve-btn" data-url="{$approveUrl}">{$approveText}</button>
            <button class="btn btn-danger btn-sm reject-btn" data-url="{$rejectUrl}">✖ {$rejectText}</button>
            HTML;
        });
    $grid->disableActions();
    $grid->disableCreation();
        return $grid;
    }
    

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(SuperadminBannerRequest::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('home_carousel_id', __('Home carousel id'));
        $show->field('coins_deducted', __('Coins deducted'));
        $show->field('status', __('Status'));
        $show->field('notes', __('Notes'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SuperadminBannerRequest());

        $form->number('user_id', __('User id'));
        $form->number('home_carousel_id', __('Home carousel id'));
        $form->number('coins_deducted', __('Coins deducted'))->default(10);
        $form->text('status', __('Status'))->default('pending');
        $form->text('notes', __('Notes'));

        return $form;
    }

    public function approve($id)
    {
        $request = SuperadminBannerRequest::findOrFail($id);
        $homeCarousel = $request->homeCarousel;
        $user = $request->user;
        $displayType = preg_replace('/^display_/', '', (string) $request->notes);
       
        $hours = (int) ($request->hours ?? 1);
    
        $now = now();
    
        $homeCarousel->update([
            'enable' => 1,
        ]);
    
        $display = HomeCarouselDisplay::where('home_carousel_id', $homeCarousel->id)
            ->where('display_type', $displayType)
            ->first();
    
        if ($display) {
            if ($display->end_at && $display->end_at->isFuture()) {
                $existingHours = $this->convertToHours($display->duration, $display->duration_unit);
    
                $totalHours = $existingHours + $hours;
    
                $newEndAt = $display->end_at->copy()->addHours($hours);
    
                $display->update([
                    'duration'      => $totalHours,
                    'duration_unit' => 'hours', 
                    'end_at'        => $newEndAt,
                ]);
            } else {
                $newEndAt = $now->copy()->addHours($hours);
    
                $display->update([
                    'duration'      => $hours,
                    'duration_unit' => 'hours',
                    'created_at'    => $now,
                    'end_at'        => $newEndAt,
                ]);
            }
        } else {
            $newEndAt = $now->copy()->addHours($hours);
    
            HomeCarouselDisplay::create([
                'home_carousel_id' => $homeCarousel->id,
                'display_type'     => $displayType,
                'duration'         => $hours,
                'duration_unit'    => 'hours',
                'created_at'       => $now,
                'end_at'           => $newEndAt,
            ]);
        }
    
        $request->update(['status' => 'approved']);
    
        return response()->json([
            'success' => true,
            'message' => __('Banner approved successfully'),
        ]);
    }
    
   
    protected function convertToHours(int $value, string $unit): int
    {
        return match ($unit) {
            'hours'  => $value,
            'days'   => $value * 24,
            'months' => $value * 30 * 24, 
            default  => $value,
        };
    }
    
    public function reject($id)
    {
        $request = SuperadminBannerRequest::findOrFail($id);

        SuperAdminHelper::addCoins($request->user_id, $request->coins_deducted);

        $request->status = 'rejected';
        $request->save();

        return response()->json(['success' => true, 'message' => 'Banner rejected successfully']);
    }

}
