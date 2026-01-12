<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Grid;
use App\Helpers\Common;
use App\Models\AdminUser;
use App\Models\UserSallary;
use Illuminate\Http\Request;
use App\Facades\ManagerHelper;
use App\Models\ShippingAgency;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use App\Admin\Controllers\MainController;

class ReportController extends MainController
{
    public $permission_name = 'reports';

    public function index(Content $content)
    {
        $name = request('name', 'users');

        $title = match ($name) {
            'users'     => __('Host reports'),
            'agencies'    => __('agencies report'),
            'agencies_manger'  => __('admin.manger'),
            default     => __('Host reports'),
        };

        return parent::index($content
            ->title($title)
            ->description(__(request('desc', 'users')))
            ->row(function ($row) {
                $row->column(2, view('admin.grid.common.actions'));
                $row->column(10, $this->grid());
            }));
    }

    protected function grid()
    {
        $name = request('name', 'users');

        abort_if(
            !method_exists($this, $name),
            404,
        );

        $grid = $this->{$name}();
        $grid->disableExport();
        $grid->disableActions();
        $grid->disableCreateButton();

        return $grid;
    }

    protected function users()
    {
        $grid = new Grid(new User());
        $countryID = session('filter_country_id');

        $grid->disableRowSelector();

        $month = request('month', now()->month);
        $year  = request('year', now()->year);

        $salarySub = DB::table('user_sallaries')
            ->selectRaw('
        user_id,
        SUM(sallary) as salary_sum,
        SUM(cut_amount) as cut_sum,
        SUM(sallary - cut_amount) as total_salary
    ')
            ->where('is_paid', 0)
            ->where('month', $month)
            ->where('year', $year)
            ->groupBy('user_id');

        $diamondSub = DB::table('user_target')
            ->selectRaw('
        user_id,
        SUM(user_diamonds) as diamonds
    ')
            ->where('add_month', $month)
            ->where('add_year', $year)
            ->groupBy('user_id');

        $grid->model()
            ->leftJoinSub(
                $salarySub,
                'salary_table',
                fn($j) =>
                $j->on('salary_table.user_id', '=', 'users.id')
            )
            ->leftJoinSub(
                $diamondSub,
                'diamond_table',
                fn($j) =>
                $j->on('diamond_table.user_id', '=', 'users.id')
            )

            ->with([
                'profile',
                'packs' => fn($q) => $q->whereIn('type', [25])->where('is_used', true)->with('ware:id,value'),
                'targets',
                'userSallary' => fn($q) => $q->where('month', $month)->where('year', $year),
                'agency'
            ])
            ->when($countryID, fn($q) => $q->where('country_id', $countryID))
            ->where('agency_id', '!=', 0)
            ->where('agency_id', '!=', '')
            ->where('agency_id', '!=', null)
            ->select([
                'users.id',
                'users.name',
                'users.uuid',
                'users.agency_id',
                DB::raw('COALESCE(diamond_table.diamonds,0) as diamonds'),
                DB::raw('COALESCE(salary_table.cut_sum,0) as expenses'),
                DB::raw('COALESCE(salary_table.total_salary,0) as total'),
            ]);

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();

            $filter->column(1/2, function ($filter) {
                $filter->equal('id', __('ID'));
            });

            $filter->column(1/2, function ($filter) {
                $filter->equal('uuid', __('Unique ID'));
            });

            $filter->column(1/2, function ($filter) {
                $filter->equal('agency_id', __('agency'))
                    ->select(Common::by_agency_filter());
            });

            $filter->column(1/2, function ($filter) {
                $filter->where(function ($query) {
                    if (!empty($this->input)) {
                        $query->whereHas('userSallary', fn($q) =>
                        $q->where('year', $this->input)
                        );
                    }
                }, __('Year'), 'year')->integer();
            });

            $filter->column(1/2, function ($filter) {
                $filter->where(function ($query) {
                    if (!empty($this->input)) {
                        $query->whereHas('userSallary', fn($q) =>
                        $q->where('month', $this->input)
                        );
                    }
                }, __('Month'), 'month')->integer();
            });
        });
        $grid->column('id', __('Id'));

        $grid->column('name', __('user'))->display(function ($name) {
            $name = @$this->name ?? '';
            $uid = @$this->uuid;
            $path = @$this?->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = $this ? url("admin/users/{$this->id}") : 0;
            return "<div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='color: #aaa; font-size: smaller;'>UUID: $uid</span>
                    </div>
                </div>";
        });
        // $grid->column('monthly_diamond_received', __('diamond'))->display(function () {
        //     $diamond = @$this->getTotalDiamond(request('month'), request('year')) ?? 0;
        //     $image = asset('images/diamond.jpg'); // Adjust path as needed
        //     return "<div style='display: flex; align-items: center; '>
        //             <span>{$diamond}</span>
        //             <img src='{$image}' alt='USD' width='20' height='20'>
        //         </div>";
        // });

        $grid->column('diamonds', __('diamond'))->display(function ($v) {
            $diamond = floor($v ?? 0);
            $image = asset('images/diamond.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>
                    <span>{$diamond}</span>
                    <img src='{$image}' alt='USD' width='20' height='20'>
                </div>";
        });
        $grid->column('target', __('target'))->display(function () {
            // return @$this->getTotalSallary(request('month'), request('year')) ?? 0;
            $lastTargetFromRelation = optional($this->targets()->orderByDesc('id')->first())->target_id ?? 0;
            return $lastTargetFromRelation;
        });
        $grid->column('expenses', __('expenses'))->display(function () {
            return @$this->getTotalCutAmount(request('month'), request('year')) ?? 0;
        });

        // $grid->column('total', __('salary'))->display(function () {
        //     $salary = $this->getSalary(request('month'), request('year')) ?? 0;
        //     $image = asset('images/dollar.jpg'); // Adjust path as needed
        //     return "<div style='display: flex; align-items: center;'>
        //             <span>{$salary}</span>
        //             <img src='{$image}' alt='USD' width='20' height='20'>
        //         </div>";
        // });

        $grid->column('total', __('salary'))->display(function ($v) {
            $salary = round($v, 2) ?? 0;
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center;'>
                    <span>{$salary}</span>
                    <img src='{$image}' alt='USD' width='20' height='20'>
                </div>";
        });
        $grid->column('sallary_year', __('Year'))->display(function () {

            $sallary = $this->userSallary()
                // ->where('month', $month)
                // ->where('year', $year)
                ->latest()
                ->first();

            return $sallary?->year ?? '-';
        });

        $grid->column('sallary_month', __('Month'))->display(function () {
            $month = request('month') ?? now()->month;
            $monthName = Carbon::create()->month($month)->translatedFormat('F'); // اسم الشهر حسب اللغة

            $sallary = $this->userSallary;
            // ->where('month', $month)
            // ->where('year', request('year', now()->year))
            // ->first();

            return $sallary ? $monthName : '-';
        });

        // $grid->column('moments_and_reels', __('Moments & Reels'))->display(function () {

        //      $sallary = $this->userSallary()->first();

        //     if (!$sallary || !$sallary->extras) {
        //         return '<span style="color: #aaa;">No Data</span>';
        //     }

        //     $extras = json_decode($sallary->extras, true);

        //     $momentUpload = $extras['moment']['upload'] ?? '-';
        //     $momentLikes = $extras['moment']['likes'] ?? '-';
        //     $momentComments = $extras['moment']['comments'] ?? '-';

        //     $reelUpload = $extras['reel']['upload'] ?? '-';
        //     $reelLikes = $extras['reel']['likes'] ?? '-';
        //     $reelComments = $extras['reel']['comments'] ?? '-';

        //     $labelMoments = __('Moments');
        //     $labelReels = __('Reels');
        //     $labelUploads = __('Uploads:');
        //     $labelLikes = __('Likes:');
        //     $labelComments = __('Comments:');

        //     return <<<HTML
        //         <div style="line-height: 1.6;">
        //             <div><b>{$labelMoments}</b></div>
        //             <ul style="margin-left: 8px;width: 149px;">
        //                 <li><b>{$labelUploads}</b> {$momentUpload}</li>
        //                 <li><b>{$labelLikes}</b> {$momentLikes}</li>
        //                 <li><b>{$labelComments}</b> {$momentComments}</li>
        //             </ul>
        //             <div><b>{$labelReels}</b></div>
        //             <ul style="margin-left: 8px;width: 149px;">
        //                 <li><b>{$labelUploads}</b> {$reelUpload}</li>
        //                 <li><b>{$labelLikes}</b> {$reelLikes}</li>
        //                 <li><b>{$labelComments}</b> {$reelComments}</li>
        //             </ul>
        //         </div>
        //     HTML;
        // });

        $grid->column('moments_and_reels', __('Moments & Reels'))->display(function () {
            $userId = $this->id;
            $month  = request('month', now()->month);
            $year   = request('year', now()->year);

            return <<<HTML
        <div
            class="moments-reels"
            data-user="{$userId}"
            data-month="{$month}"
            data-year="{$year}"
            style="cursor:pointer;color:#3c8dbc"
        >
            <i class="fa fa-spinner fa-spin"></i> Loading...
        </div>
    HTML;
        });

        Admin::script(<<<JS
        $('.moments-reels').each(function () {
            let el = $(this);

            $.get('/admin/moments-reels', {
                user_id: el.data('user'),
                month: el.data('month'),
                year: el.data('year')
            }, function (res) {
                el.html(res.html);
            });
        });
        JS);
        $grid->column('agency', __('agency'))->display(function () {
            $name = @$this->agency->name ?? '';
            $path = @$this->agency->img;
            $defaultImage = asset("images/icon-agency.jpg");
            $url = getImagePath($path) ?? $defaultImage;
            $showUrl = $this->agency ? url("admin/agencies/profile/{$this->agency->id}") : 0;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                    $image
                    <span>$name</span>
                </a>
            </div>
        ";
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('<a href="' . route('custom-export-users', ['month' => request('month'), 'year' => request('year'), 'agency_id' => request('agency_id'), 'id' => request('id')]) . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-download"></i>' . __('admin.exportExcel') . '</a>');
        });

        return $grid;
    }

    protected function agencies(): Grid
    {
        $grid = new Grid(new Agency());
        $countryID = session('filter_country_id');

        $grid->disableRowSelector();

        $grid->model()
            ->when($countryID, fn($q) => $q->where('country_id', $countryID))
            ->withCount(['users'])
            ->with(['owner.profile']);

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();

            $filter->disableIdFilter();

            $filter->equal('id', __('dashboard.agency'))
                ->select()
                ->ajax(route('admin.filter-agencies'));

            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    $year = request('year');
                    if (!empty($year)) {
                        $query->whereHas('agencySalaries', fn($q) => $q->where('year', $year));
                    }
                }, __('Year'), 'year')->integer();
            });

            $filter->where(function ($query) {
                $month = request('month');
                if (!empty($month)) {
                    $query->whereHas('agencySalaries', fn($q) => $q->where('month', $month));
                }
            }, __('Month'), 'month')->integer();
        });
        $grid->column('id', __('ID'));

        $grid->column('agency', __('dashboard.agency'))->display(function () {
            $defaultImage = asset('images/icon-agency.jpg');
            $url = getImagePath($this->img) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $profileUrl = route('admin.agency.profile', ['id' => $this->id]);

            return "<a href='{$profileUrl}' style='text-decoration: none; color: inherit;'>
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            <img src='$url' style='height: 40px !important; width: 40px !important; object-fit: cover;' />
                            <div style='display: flex; flex-direction: column;'>
                                <span style='text-decoration: underline; cursor: pointer;'>{$this->name}</span>
                                <span style='font-size: smaller;'>ID: {$this->id}</span>
                            </div>
                        </div>
                    </a>";
        });

        $grid->column('target', __('target'))->display(function () {
            return @$this->getTotalTargetAgency(request('month'), request('year')) ?? 0;
        });
        $grid->column('net_salary', __('Net Salary'))->display(function () {
            return @$this->getTotalNetSallaryAgency(request('month'), request('year')) ?? 0;
        });
        $grid->column('expenses', __('expenses'))->display(function () {
            return @$this->getTotalCutAmountAgency(request('month'), request('year')) ?? 0;
        });


        $grid->column('total', __('salary'))->display(function () {
            $salary = $this->getSalaryWithOutCutAmountAgency(request('month'), request('year')) ?? 0;
            $image = asset('images/dollar.jpg');
            return "<div style='display: flex; align-items: center;'>
                    <span>{$salary}</span>
                    <img src='{$image}' alt='USD' width='20' height='20'>
                </div>";
        });

        $grid->column('hosts', __('dashboard.hosts'))->display(function () {
            return '<a href="?name=users&desc=' . $this->name . '&aid=' . $this->id . '">' . $this->users_count . '</a>';
        });

        $grid->tools(function (Grid\Tools $tools) {
            $query = http_build_query([
                'id' => request('id'),
                'month' => request('month'),
                'year' => request('year'),
            ]);

            $tools->append('<a href="' . route('agency-export-report') . '?' . $query . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-download"></i> ' . __('admin.exportExcel') . '</a>');
        });


        return $grid;
    }

    protected function agencies_manger()
    {
        $grid = new Grid(new AdminUser());
        $countryID = session('filter_country_id');

        $grid->disableRowSelector();
        $grid->model()->with([
            'user',
            'user.profile',
             'user.packs' => fn($q) => $q->whereIn('type', [25])->where('is_used', true)->with('ware:id,value')
        ])
            ->when($countryID, fn($q) => $q->whereHas('user', fn($q) => $q->where('country_id', $countryID)))
            ->where('app_id', '!=', 0);

        $grid->column('user.id', __('Id'));

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('user.id', 'User ID');
        });
        $grid->column('user.name', __('name'))->display(function ($name) {
            $uid = @$this->user->uuid;
            $path = @$this?->user->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = $this->user ? url("admin/users/{$this->user->id}") : 0;
            return "<div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='color: #aaa; font-size: smaller;'>UUID: $uid</span>
                    </div>
                </div>";
        });;

        $grid->column('due', __('due'))->display(function ($_) {
            $salary = ManagerHelper::getTotalAgenciesSalary($this->managerAgenciesWithoutScope()->get(), $this->app_id);
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>
                    <span>{$salary}</span>
                    <img src='{$image}' alt='USD' width='20' height='20'>
                </div>";
        });

        $grid->export(function ($export) {
            $export->filename('report');
            $export->column('uuid', function ($value, $original) {
                return $value;
            });
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('<a href="' . route('admin.agency-manger-export', [
                'user_id' => request('user.id') ?? request('filters.user.id') ?? null
            ]) . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-download"></i> ' . __('admin.exportExcel') . '</a>');
        });
        $grid->disableExport();

        return $grid;
    }




    public function momentsReels(Request $request)
    {
        $userId = $request->user_id;
        $month  = $request->month;
        $year   = $request->year;
//  dd( $userId,$month, $year);
        $salary = UserSallary::query()
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$salary || empty($salary->extras)) {
            return response()->json([
                'html' => '<span style="color:#aaa">No Data</span>'
            ]);
        }

        $extras = json_decode($salary->extras, true);

        return response()->json([
            'html' => view('moments-reels', [
                'extras' => $extras
            ])->render()
        ]);
    }
}
