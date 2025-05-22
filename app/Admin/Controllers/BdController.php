<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\DeleteBdAction;
use App\Admin\Actions\MakeBdDefultAction;
use App\Models\Bd;
use App\Models\BDSallary;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Carbon;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Cache;

class BdController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'BD';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Bd());

        $grid->column('id', __('Id'));
        $grid->column('username', __('username'));
        $grid->column('name', __('Name'));
    
        $grid->column('appUser.name', __('المستخدم المرتبط'))->display(function ($name) {
            $user = $this->appUser;
            if (!$user) return "<span style='color: red;'>غير مرتبط</span>";
    
            $uid = $user->uuid ?? 'غير معروف';
            $path = $user->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;
    
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
    
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = url("admin/users/{$user->id}");
    
            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='font-size: smaller;'>UUID: $uid</span>
                    </div>
                </div>
            ";
        });

        $grid->column('agencies_count', __('Agencies Count'))->display(function () {
            return $this->agencies_count;
        });
        
        $grid->column('total_salary', __('total salary'))->display(function () {
            return number_format($this->total_salary, 2);
        });
        
        $grid->column('net_salary', __('Net Salary'))->display(function () {
            return number_format($this->net_salary, 2);
        });
    
        $grid->column('created_at', __('Created at'))->display(function ($date) {
            $carbonDate = Carbon::parse($date);
            $locale = App::getLocale();
            $carbonDate->locale($locale);
            return $carbonDate->translatedFormat('d F Y H:i'); // مثال: 22 مايو 2025 14:30
        });    


        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $model = $actions->row;
            $actions->add(new \App\Admin\Actions\DeleteBdAction());
            $actions->add(new MakeBdDefultAction($model->id));

        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    // protected function detail($id)
    // {
    //    return $this->profile($id);
    // }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
            return $content
            ->title(trans(''))
            ->body($this->profile($id));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Bd());

        $form->text('username', __('username'))->rules('required');
        $form->password('password', __('Password'))->rules('required');
        $form->text('name', __('Name'));
        $form->image('avatar', __('img'));


        if ($form->isEditing()){
        $form->select('app_id', __('validation.select_user'))->options(function ($value) {
            $ops2 = [];
            foreach (User::Where('id', $value)->get() as $user) {
                $ops2[$user->id] = $user->uuid . '_' . $user->name;
            }
            return $ops2;
        })->ajax('/api/search/users3', 'id', 'name') ->rules('required')   ->help('لا يمكن التعديل إلا إذا لم يكن هناك مستخدم مرتبط، أو كان المستخدم مرتبطًا لكن تم حذفه.');
    }else {
        $form->select('app_id', __('validation.select_user'))->options(function ($value) {
            $ops2 = [];
            foreach (User::Where('id', $value)->get() as $user) {
                $ops2[$user->id] = $user->uuid . '_' . $user->name;
            }
            return $ops2;
        })->ajax('/api/search/users3', 'id', 'name')->rules('required') ;
    }

        $form->hidden('type', __('Type'))->value('bd');

        $form->saving(function (Form $form) {
            $originalAppId = $form->model()->getOriginal('app_id');

            if ($originalAppId && $originalAppId != $form->app_id) {
                $userExists = \App\Models\User::find($originalAppId);
        
                if ($userExists) {
                    admin_error('تحذير', 'app_user_change_denied');
                    $form->app_id = $originalAppId;  
                }
            }
        
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });
        return $form;
    }



    public function profile( $id)
    { 
        $year = request('year') ?? now()->year;
        $month = request('month') ?? now()->month;
        $tab = request()->query('tab', 'agencies');
    
        $bd = Cache::remember("bd_{$id}", 600, function () use ($id) {
            return Bd::
          
            select('id', 'name', 'app_id', 'avatar', 'username', 'default')->findOrFail($id);
        });
        $id = $bd->app_id;
        $defaultImage = asset("images/icon-agency.jpg");
        $imageUrl = getImagePath($bd->img) ?? $defaultImage;
    
        if (!isImageExists($imageUrl)) {
            $imageUrl = $defaultImage;
        }
    
        $bd->display_image = $imageUrl;
    
        $agencies = $transactions = $target_history = null;
    
        switch ($tab) {
            case 'agencies':
                $agencies = Cache::remember("bd_{$id}_agencies_page_" . request()->get('agencies_page', 1), 600, function () use ($bd) {
                    return $bd->agencies()->paginate(10, ['*'], 'agencies_page');
                });
                break;
    
            case 'transactions':
                $transactions = Cache::remember("bd_{$id}_transactions_page_" . request()->get('transactions_page', 1), 600, function () use ($bd) {
                    return $bd->transactions()
                        ->select('id', 'agency_id', 'user_id', 'usd', 'amount', 'created_at', 'user_charger_type', 'user_type')
                        ->latest()
                        ->paginate(10, ['*'], 'transactions_page');
                });
                
                break;
            case 'target_history':
                $target_history = Cache::remember("bd_{$id}_target_history_{$year}_{$month}_page_" . request()->get('target_history_page', 1), 600, function () use ($bd, $year, $month) {
                    return BDSallary::select(
                            'id',
                            'bd_id',
                            'agency_id',
                            'sallary',
                            'cut_amount',
                            'month',
                            'year',
                            'is_paid',
                            'created_at',
                            'total_agency_sallary',
                            'total_users_sallary',
                            'total_diamond'
                        )
                        ->where('bd_id', $bd->app_id)
                        ->where('month', $month)
                        ->where('year', $year)
                        ->latest()
                        ->paginate(10, ['*'], 'target_history_page');
                });
                            
                    break;

        }
    
        return view('admin.bd.bd_profile', compact('bd', 'agencies', 'transactions','target_history'));
    }

}
