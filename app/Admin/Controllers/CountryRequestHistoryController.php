<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use Encore\Admin\Grid;
use App\Models\Country;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use App\Admin\Services\UserService;
use Illuminate\Support\Facades\App;
use App\Models\ChangeCountryRequest;
use App\Admin\Controllers\MainController;



class CountryRequestHistoryController extends MainController
{

    public $permission_name = 'country-request-history';

    public function index(Content $content)
    {
        return $content
            ->title(trans('Change Country Requests'))
            ->body($this->grid());
    }



    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ChangeCountryRequest());
        $grid->model()
            ->with([
                'country',
                'user.profile',
                'user',
                'user.country',
                'user.receiverLevel',
                'user.senderLevel',
                'oldCountry',
                'user.packs' => fn($q) => $q->whereIn('type', [25])->where('is_used', true)->with('ware:id,value'),
            ])->where('status', '!=', 'pending')->orderByDesc('created_at');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();

            $filter->equal('status', __('Status'))->select([

                'accepted' => __('Accepted'),
                'rejected' => __('Rejected')
            ]);

            $filter->equal('country_id', __('Country'))->select(
                Country::all()->pluck(
                    App::getLocale() === 'ar' ? 'name' : 'e_name',
                    'id'
                )
            );


            $filter->column(1 / 2, function ($filter) {
                $filter->equal('user.uuid', __('uuid'));
            });
        });

        Admin::style(UserService::adminUserCardStyles() . gridStyles());
        $grid->id(__('ID'));

        $grid->column('name', __('user'))
            ->display(function ($name) {

                $user = $this->user;
                if (! $user) {
                    return __('No User');
                }
                $showUrl = url("admin/users/{$user->id}");
                return app(UserService::class)->adminUserCard($user, withoutLevels: true, showUrl: $showUrl);
            });

        $grid->column('user_country', __('country user'))->display(function () {

            $country = $this->oldCountry;

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


        $grid->column('created_at', __('Created At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });

        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->disableActions();
        $grid->disableCreateButton();
        return $grid;
    }
}
