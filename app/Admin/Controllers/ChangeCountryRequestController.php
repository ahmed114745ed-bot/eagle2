<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\Country;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Admin\Services\UserService;
use App\Models\ChangeCountryRequest;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\HasResourceActions;
use App\Admin\Actions\Grid\ActionCountryRequest;
use Illuminate\Http\Request;



class ChangeCountryRequestController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'change-country-request';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Change Country Requests'))
            ->body($this->grid()));
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('Change Country Requests'))
            ->body($this->detail($id)));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ChangeCountryRequest());
        $grid->model()->with(['country', 'user.packs'])->where('status', 'pending')->orderByDesc('created_at');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();

            $filter->equal('status', __('Status'))->select([
                // 'pending' => 'Pending',
                'accepted' => 'Accepted',
                'rejected' => 'Rejected'
            ]);

            $filter->equal('country_id', __('Country'))->select(
                Country::pluck('e_name', 'id')
            );
        });

        $grid->id(__('ID'));

        $grid->column('name', __('Name'))
            ->display(function ($name) {

                $user = $this->user;
                if (! $user) {
                    return __('No User');
                }
                $showUrl = url("admin/users/{$user->id}");
                return app(UserService::class)->adminUserAvatar($user, withoutLevels: true, showUrl: $showUrl);
            });

        $grid->column('country.name', __('country user'))->display(function () {

            $country = $this->user->country;

            if (!$country) {
                return '-';
            }

            // Select correct name based on locale
            $name = app()->getLocale() === 'ar'
                ? ($country->name ?: $country->e_name)
                : ($country->e_name ?: $country->name);

            // Get flag image URL
            $flag = $country->flag ? getImagePath($country->flag) : null;


            return <<<HTML
                    <div style="display:flex; align-items:center; gap:8px;">
                        <img src="$flag" alt="flag" width="20" height="20" style="border-radius:4px;">
                        <span>$name</span>
                    </div>
                HTML;
        });

        $grid->column('country.name', __('country'))->display(function () {

            $country = $this->country;

            if (!$country) {
                return '-';
            }

            // Select correct name based on locale
            $name = app()->getLocale() === 'ar'
                ? ($country->name ?: $country->e_name)
                : ($country->e_name ?: $country->name);

            // Get flag image URL
            $flag = $country->flag ? getImagePath($country->flag) : null;


            return <<<HTML
                    <div style="display:flex; align-items:center; gap:8px;">
                        <img src="$flag" alt="flag" width="20" height="20" style="border-radius:4px;">
                        <span>$name</span>
                    </div>
                HTML;
        });



        $grid->column('status', trans('Status'))->display(function ($value) {
            $colors = [
                'pending' => 'warning',
                'accepted' => 'success',
                'rejected' => 'danger'
            ];
            $color = $colors[$value] ?? 'default';
            return "<span class='label label-{$color}'>" . __(ucfirst($value)) . "</span>";
        });

        if (Admin::user()->can('status-switch-' . $this->permission_name) || Admin::user()->can('*')) {
            $grid->column('action', trans('Action'))->display(function () {
                if ($this->status === 'pending') {
                    $acceptUrl = admin_url("country-requests/{$this->id}/accept");
                    $rejectUrl = admin_url("country-requests/{$this->id}/reject");

                    $acceptText = __('Accept');
                    $rejectText = __('Reject');

                    return <<<HTML
                        <a href="{$acceptUrl}" class="btn btn-success btn-xs">
                            <i class="fa fa-check"></i> {$acceptText}
                        </a>
                        <a href="{$rejectUrl}" class="btn btn-danger btn-xs">
                            <i class="fa fa-times"></i> {$rejectText}
                        </a>
                    HTML;
                }
                return '-';
            });
        }

        $grid->column('created_at', __('Created At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
            $batch->add(new ActionCountryRequest());
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('<a href="' . url('/admin/country-requests-actions') . '"  class="btn btn-sm btn-success">' . __('admin.history') . '</a>');
        });
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->disableActions();
        $grid->disableCreateButton();
        return $grid;
    }


    public function changeCountry(Request $request)
    {
        if ($request->change_country == "true") {
            settings()->set("change_country", "1");
        } else {
            settings()->set("change_country", "0");
        }
    }
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ChangeCountryRequest::findOrFail($id));

        $show->id('ID');
        $show->field('country.e_name', trans('Country'));
        $show->field('user_id', trans('User ID'));
        $show->field('status', trans('Status'));
        $show->field('created_at', trans('Created At'));
        $show->field('updated_at', trans('Updated At'));

        $this->extendShow($show);
        return $show;
    }

    public function accept($id)
    {
        $request = ChangeCountryRequest::findOrFail($id);

        if ($request->status !== 'pending') {
            admin_toastr('This request has already been processed', 'error');
            return redirect()->back();
        }

        $request->status = 'accepted';
        $request->save();

        $user = User::find($request->user_id);
        $user->country_id = $request->country_id;
        $user->save();

        $title = __('Change Country Request');
        $body = __('Your country change request has been accepted');
        Common::sendOfficialMessage($user->id, $title, $body);
        Common::send_firebase_notification($user->notification_id, $title, $body);

        admin_toastr(__('Request accepted successfully'), 'success');
        return redirect()->back();
    }

    public function reject($id)
    {
        $request = ChangeCountryRequest::findOrFail($id);

        if ($request->status !== 'pending') {
            admin_toastr(__('This request has already been processed'), 'error');
            return redirect()->back();
        }

        $request->status = 'rejected';
        $request->save();

        $user = User::find($request->user_id);

        $title = __('Change Country Request');
        $body = __('Your country crhange request has been rejected');
        Common::sendOfficialMessage($user->id, $title, $body);
        Common::send_firebase_notification($user->notification_id, $title, $body);

        admin_toastr(__('Request rejected successfully'), 'success');
        return redirect()->back();
    }
}
