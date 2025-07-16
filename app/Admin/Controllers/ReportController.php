<?php

namespace App\Admin\Controllers;

use App\Models\ShippingAgency;
use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Grid;
use App\Helpers\Common;
use App\Models\AdminUser;
use App\Facades\ManagerHelper;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use Carbon\Carbon;

class ReportController extends MainController
{
    public $permission_name = 'reports';

    public function index(Content $content)
    {
        checkAgencyFeature();

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
        $grid->disableRowSelector();
        $grid->model()
            ->where('agency_id', '!=', 0)
            ->where('agency_id', '!=', '')
            ->where('agency_id', '!=', null);

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('uuid', __('uuid'));
            });

            $filter->column(1 / 2, function ($filter) {
                 $filter->equal('agency_id', __('agency'))->select(Common::by_agency_filter());
                $filter->where(function ($query) {
                    $year = request('year');
                    if (!empty($year)) {
                        $query->whereHas('userSallary', function ($q) use ($year) {
                            $q->where('year', $year);
                        });
                    }
                }, __('Year'), 'year')->integer();
            });

            $filter->where(function ($query) {
                $month = request('month');
                if (!empty($month)) {
                    $query->whereHas('userSallary', function ($q) use ($month) {
                        $q->where('month', $month);
                    });
                }
            }, __('Month'), 'month')->integer();
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
        $grid->column('monthly_diamond_received', __('diamond'))->display(function () {
            $diamond = @$this->getTotalDiamond(request('month'), request('year')) ?? 0;
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

        $grid->column('total', __('salary'))->display(function () {
            $salary = $this->getSalary(request('month'), request('year')) ?? 0;
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center;'>
                    <span>{$salary}</span>
                    <img src='{$image}' alt='USD' width='20' height='20'>
                </div>";
        });
        $grid->column('sallary_year', __('Year'))->display(function () {
            $year = request('year') ?? now()->year;
            $month = request('month') ?? now()->month;

            $sallary = $this->userSallary()
                ->where('month', $month)
                ->where('year', $year)
                ->latest()
                ->first();

            return $sallary?->year ?? '-';
        });

        $grid->column('sallary_month', __('Month'))->display(function () {
            $month = request('month') ?? now()->month;
            $monthName = Carbon::create()->month($month)->translatedFormat('F'); // اسم الشهر حسب اللغة

            $sallary = $this->userSallary()
                ->where('month', $month)
                ->where('year', request('year', now()->year))
                ->first();

            return $sallary ? $monthName : '-';
        });

        $grid->column('moments_and_reels', __('Moments & Reels'))->display(function () {
            $month = request('month') ?? now()->month;
            $year = request('year') ?? now()->year;

            $sallary = $this->userSallary()
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if (!$sallary || !$sallary->extras) {
                return '<span style="color: #aaa;">No Data</span>';
            }

            $extras = json_decode($sallary->extras, true);

            $momentUpload = $extras['moment']['upload'] ?? '-';
            $momentLikes = $extras['moment']['likes'] ?? '-';
            $momentComments = $extras['moment']['comments'] ?? '-';

            $reelUpload = $extras['reel']['upload'] ?? '-';
            $reelLikes = $extras['reel']['likes'] ?? '-';
            $reelComments = $extras['reel']['comments'] ?? '-';

            $labelMoments = __('Moments');
            $labelReels = __('Reels');
            $labelUploads = __('Uploads:');
            $labelLikes = __('Likes:');
            $labelComments = __('Comments:');

            return <<<HTML
                <div style="line-height: 1.6;">
                    <div><b>{$labelMoments}</b></div>
                    <ul style="margin-left: 8px;width: 149px;">
                        <li><b>{$labelUploads}</b> {$momentUpload}</li>
                        <li><b>{$labelLikes}</b> {$momentLikes}</li>
                        <li><b>{$labelComments}</b> {$momentComments}</li>
                    </ul>
                    <div><b>{$labelReels}</b></div>
                    <ul style="margin-left: 8px;width: 149px;">
                        <li><b>{$labelUploads}</b> {$reelUpload}</li>
                        <li><b>{$labelLikes}</b> {$reelLikes}</li>
                        <li><b>{$labelComments}</b> {$reelComments}</li>
                    </ul>
                </div>
            HTML;
        });
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
        $grid->disableRowSelector();

        $grid->model()
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
        // $grid->column('owner.name', __('owner'))->display(function ($name) {
        //     $uid = $this->owner?->uuid;
        //     $path = $this->owner?->profile?->avatar;
        //     $defaultImage = asset('images/businessman-icon.jpg');
        //     $url = getImagePath($path) ?? $defaultImage;

        //     // Check if the image exists
        //     if (!isImageExists($url)) {
        //         $url = $defaultImage;
        //     }

        //     $showUrl = $this->owner ? url("admin/users/{$this->owner->id}") : '#';

        //     $image = handleShowImageWithTypes($this->id, $url, 40, 40);

        //     return "<div style='display: flex; align-items: center; gap: 10px;'>
        //             {$image}
        //             <div>
        //                 <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
        //                     <span style='text-decoration: underline; cursor: pointer;'>$name</span>
        //                 </a>
        //                 <span style='font-size: smaller;'>UUID: $uid</span>
        //             </div>
        //         </div>";
        // });
        $grid->column('hosts', __('dashboard.hosts'))->display(function () {
            return '<a href="?name=users&desc=' . $this->name . '&aid=' . $this->id . '">' . $this->users_count . '</a>';
        });

        // $grid->tools(function (Grid\Tools $tools) {
        //     $tools->append('<a href="' . route('agency-export-report', ['month' => request('month'), 'year' => request()->year, 'agency_id' => request()->id]) . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-download"></i> ' . __('admin.exportExcel') . '</a>');
        // });
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
        $grid->disableRowSelector();
        $grid->model()
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
}
