<?php

namespace App\Admin\Controllers\AgencyControllers;

use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\UserTarget;
use App\Models\UserSallary;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\HasResourceActions;

class UserTargetController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'achieved-Target';

    public function index(Content $content)
    {
        checkAgencyFeature();

        return parent::index($content
            ->title(trans('Achieved Target'))
            ->body($this->grid()));
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserTarget);
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('user.uuid', __('uuid'));
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('add_month', __('month'));
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('add_year', __('year'));
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->equal('agency.id', __('agency id'));
            });
        });
        $grid->model()->ofAgency()->where('agency_obtain', '>', 0);
        $grid->id('ID');
        $grid->column('user_id', __('user'))->display(function ($name) {
            $name = @$this->user->name ?? '';
            $uid = @$this->user->uuid ?? '';
            if (request()->filled('_export_')) {
                return "{$name} (UUID: {$uid})";
            }
            $path = @$this->user?->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl =  ($this->user) ? url("admin/users/{$this->user->id}") : 0;
            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='color: #aaa; font-size: smaller;'>UUID: $uid</span>
                    </div>
                </div>
            ";
        });
        $grid->column('agency_id', __('agency'))->display(function () {
            if (request()->filled('_export_')) {
                return $name ?? '';
            }
            if (!@$this->agency) {
                return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>unknown agency</span>
            </div>
        ";
            }
            $name = @$this->agency->name ?? '';
            if (request()->filled('_export_')) {
                return $name ?? '';
            }
            $path = @$this->agency->img;
            $defaultImage = asset("images/icon-agency.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = url("admin/agencies/profile/{$this->agency->id}");
            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                 <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
            </div>
        ";
        });
        $grid->column('add_month', __('date'))->display(function ($month) {
            return $month . '/' . $this->add_year;
        });
        $grid->column('target_id', __('target id'));
        $grid->column('target_diamonds', __('target diamonds'));

        $grid->column('target_usd', __('usd') . ' ' . __('deserved') . '(%)');
        $grid->column('target_hours', __('target hours'))->display(function ($usd) {
            return $usd . '/' . $this->target_hours;
        });
        $grid->column('target_days', __('target days'))->display(function ($usd) {

            return $usd . '/' . $this->target_days;
        });
        $grid->column('target_agency_share', __('agency share') . '(%)');
        $grid->column('user_diamonds', __('user diamonds'));
        $grid->column('user_hours', __('user hours'));
        $grid->column('user_days', __('user days'));
        $grid->column('moments_and_reels', __('Moments & Reels'))->display(function () {

            $extras = $this->extras ?? [];

            if (is_string($extras)) {
                $extras = json_decode($extras, true) ?: [];
            }
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
            if (request()->filled('_export_')) {
                return "Moments:\nUploads: $momentUpload, Likes: $momentLikes, Comments: $momentComments\n" .
                    "Reels:\nUploads: $reelUpload, Likes: $reelLikes, Comments: $reelComments";
            }
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
        $grid->column('user_obtain', __('salary'))->display(function ($usd) {
            $usd =  truncateAndTrim($usd);
            if (request()->filled('_export_')) {
                return $usd ?? 0;
            }
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->column('withdrawal', __('withdrawal'))->display(function () {
            $userSalary = UserSallary::query()->where('user_id', $this->user_id)
                ->where('month', $this->add_month)

                ->where('year', $this->add_year)
                ->where('target_id', $this->target_id)
                ->where('user_agency_id', $this->agency_id)
                ->value('cut_amount') ?? 0;
            $userSalary =  truncateAndTrim($userSalary);
            if (request()->filled('_export_')) {
                return $userSalary;
            }

            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$userSalary}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->column('net_salary', __('net salary'))->display(function () {
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            $userSalary = UserSallary::query()->where('user_id', $this->user_id)
                ->where('month', $this->add_month)

                ->where('year', $this->add_year)
                ->where('target_id', $this->target_id)
                ->where('user_agency_id', $this->agency_id)
                ->selectRaw('sallary - cut_amount AS net_salary')
                ->value('net_salary') ?? 0;
            $userSalary =  truncateAndTrim($userSalary);
            if (request()->filled('_export_')) {
                return $userSalary;
            }
            return "<div style='display: flex; align-items: center; '>

                        <span>{$userSalary}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->column('agency_obtain', __('agency salary'))->display(function ($usd) {
            $usd =truncateAndTrim($usd);
            if (request()->filled('_export_')) {
                return $usd ?? 0;
            }
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->disableActions();
        $grid->disableCreateButton();
        return $grid;
    }
}
