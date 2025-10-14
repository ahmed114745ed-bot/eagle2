<?php

namespace App\SuperAdmin\Controllers;

use App\Models\Bd;
use App\Models\BdAgencyHostSallary;
use App\Models\SuperAdmin;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Carbon;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Routing\Controller;
use Encore\Admin\Facades\Admin;


class ProfessionalBdController extends Controller
{
   

    public function index(Content $content)
    {
        return $content
            ->title(__('professional BD'))
           
            ->row(function ($row) {
                $row->column(12, $this->grid());
            });
    }



    

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Bd());
         $authCountryId = Admin::user()->country_id;
        

        $grid->model()->where('country_id','!=',$authCountryId)
            

            ->with(['bdSalaries', 'appUser.packs', 'appUser.profile', 'parent.appUser.packs'])
            ->withSum('bdSalaries', 'salary')
            ->withSum('bdSalaries', 'cut_amount')
            ->withCount('agencies as total_agencies')
            ->orderByDesc('id');

        $grid->filter(function ($filter) {
            $filter->like('appUser.uuid', __('App User UUID'));
            $filter->like('appUser.name', __('User Name'));
        });
        $grid->column('id', __('Id'));
        $grid->column('username', __('Bd'))->display(function ($name) {
            if (request()->filled('_export_')) {
                return $name;
            }

            $id = $this->id ?? '-';
            $name = $this->username ?? 'غير معروف';
            $path = $this->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = url("admin/usersBd/{$this->id}");

            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='font-size: smaller;'>ID: $id</span>
                    </div>
                </div>
            ";
        });
        $grid->column('default', __('default_status'))->display(function () {
            if (request()->filled('_export_')) {
                return $this->default;
            }

            if ($this->default == 1) {
                return <<<HTML
                    <span style="display: flex; align-items: center;">
                        <span style="
                            font-size: smaller;
                            background: red;
                            display: inline-block;
                            border-radius: 50%;
                            width: 10px;
                            height: 10px;
                            margin-left: 5px;
                        " title=""></span>
                    </span>
                HTML;
            } else {
                return '<span style="color: #999;"></span>';
            }
        });

        $grid->column('appUser.name', __('user'))->display(function ($name) {
            $user = $this->appUser;
            if (request()->filled('_export_')) {
                return $name;
            }
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

        $grid->column('parent.name', __('Super Admin'))->display(function () {
            $user = $this->parent?->appUser;
            $name = $user->name ?? '';

            if (request()->filled('_export_')) {
                return $name;
            }
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
            return $this->total_agencies;
        });


        $grid->column('total_salary', __('total proft'))->display(function () {
            return truncateAndTrim($this->bd_salaries_sum_salary ?? 0, 2);
        });

        $grid->column('current_balance', __('current_balance'))->display(function () {
            $total = floatval($this->bd_salaries_sum_salary ?? 0);
            $cut   = floatval($this->bd_salaries_sum_cut_amount ?? 0);
            return truncateAndTrim($total - $cut, 2);
        });

        $grid->column('total_cut', __('Cut amount'))->display(function () {
            return truncateAndTrim($this->bd_salaries_sum_cut_amount ?? 0, 2);
        });

        $grid->column('country.name', __('country'));

        

        $grid->column('created_at', __('Created at'))->display(function ($date) {
            $carbonDate = Carbon::parse($date);
            $locale = App::getLocale();
            $carbonDate->locale($locale);
            return $carbonDate->translatedFormat('d F Y H:i'); // مثال: 22 مايو 2025 14:30
        });

       
        $grid->disableRowSelector();
        $grid->disableCreateButton();
         $grid->disableActions();

        $this->extendGrid($grid);
        return $grid;
    }

   
    

  public function show($id, Content $content)
    {
        return  $content
            ->title(trans('BD'))
            ->body($this->profile($id));
    }

    public function profile($id)
    {
        $year = request('year') ?? now()->year;
        $month = request('month') ?? now()->month;
        $tab = request()->query('tab', 'agencies');

        $bd = Bd::select('id', 'name', 'app_id', 'avatar', 'username', 'default')->findOrFail($id);

        $id = $bd->id;
        $defaultImage = asset("images/icon-agency.jpg");
        $imageUrl = getImagePath($bd->avatar);
        if (!isImageExists($imageUrl)) {
            $imageUrl = $defaultImage;
        }
        $bd->display_image = $imageUrl;

        $agencies = $transactions = $target_history = null;

        switch ($tab) {
            case 'agencies':
                $agencies = $bd->agencies()->paginate(10, ['*'], 'agencies_page');
                break;

            case 'transactions':
                $transactions = $bd->transactions()
                    ->select('id', 'agency_id', 'user_id', 'usd', 'amount', 'created_at', 'user_charger_type', 'user_type')
                    ->with('receiveragency')
                    ->latest()
                    ->paginate(10, ['*'], 'transactions_page');
                break;

            case 'target_history':
                $target_history = BdAgencyHostSallary::select(
                    'id',
                    'bd_id',
                    'agency_id',
                    // 'salary',
                    'amount',
                    'month',
                    'year',
                    'bd_user_id',
                    'created_at'
                )
                    ->where('bd_id', $bd->id)
                    ->where('bd_id', $bd->id)
                    ->where('amount', '!=', 0)
                    ->where('year', $year)
                    ->latest()
                    ->paginate(10, ['*'], 'target_history_page');
                break;
        }

        return view('admin.bd.bd_profile', compact('bd', 'agencies', 'transactions', 'target_history'));
    }


    protected function detail($id)
    {
        $show = new Show(Bd::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('Username'));
        $show->field('avatar', __('Avatar'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('app_id', __('App id'));

        $this->extendShow($show);

        return $show;
    }

    public function sync($days = 0)
    {
        $days = request()->query('days', 0);
        $bds = DB::table('admin_users')
            ->where('type', 'bd')
            ->where('app_id', '!=', 0)
            ->get();

        $updated = 0;

        foreach ($bds as $bd) {
            $query = DB::table('agencies')
                ->where('bd_id', $bd->app_id);

            if ($days > 0) {
                $query->where('created_at', '<=', now()->subDays($days));
            }

            $affected = $query->update(['bd_id' => $bd->id]);
            $updated += $affected;
        }

        return response()->json([
            'status' => 'success',
            'message' => $updated
        ]);
    }
}
