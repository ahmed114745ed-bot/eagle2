<?php

namespace App\Admin\Controllers\AgencyControllers;

use App\Admin\Controllers\MainController;
use App\Admin\Services\AgencyService;
use App\Admin\Services\UserService;
use App\Helpers\UserCommon;
use App\Models\GiftLog;
use Carbon\Carbon;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class HostDiamondController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'host-diamond';

    public function index(Content $content)
    {
        checkAgencyFeature();
        $this->arabicToEnglishDates();

        return parent::index($content
            ->title(trans('Host Diamond'))
            ->body($this->grid()));
    }

    public function arabicToEnglishDates()
    {
        if (request()->has('from_date')) {
            request()->merge([
                'from_date' => UserCommon::arabicToEnglishNumbers(request('from_date')),
            ]);
        }
        if (request()->has('to_date')) {
            request()->merge([
                'to_date' => UserCommon::arabicToEnglishNumbers(request('to_date')),
            ]);
        }
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GiftLog());
        $grid->model()
            ->selectRaw('receiver_id, agency_id, SUM(giftPrice) as total_gift_price')
            ->with(['receiver.profile', 'agency']) // assuming these are relationships
            ->where('agency_id', '!=', 0)
            ->groupBy('receiver_id', 'agency_id')
            ->when(! request('from_date'), fn ($q) => $q->where('created_at', '>=', now()->startOfMonth()))
            ->when(! request('to_date'), fn ($q) => $q->where('created_at', '<=', now()->endOfMonth()))
            ->when(request('total_gift_price'), fn ($q) => $q->havingRaw('total_gift_price >= ?', [(int) request('total_gift_price')]))
            ->orderByDesc('total_gift_price');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1 / 2, function (Grid\Filter $filter) {
                $filter->equal('receiver.uuid', __('uuid'));
                $filter->where(function ($query) {
                    $tz = getTimezone();

                    $input = $this->input ?? now()->startOfMonth();
                    $date = UserCommon::arabicToEnglishNumbers($input);

                    $utcDate = Carbon::parse($date, $tz)->timezone('UTC')->startOfDay();

                    $query->where('created_at', '>=', $utcDate);
                }, __('from_date'), 'from_date')
                    ->date()
                    ->default(request('from_date'));

                $filter->where(function ($query) {
                    }, __('Greater than diamond'), 'total_gift_price')->integer();
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->equal('agency.id', __('agency id'));
                $filter->where(function ($query) {
                    $tz = getTimezone();
                    $input = $this->input ?? now()->endOfMonth();
                    $date = UserCommon::arabicToEnglishNumbers($input);
                    $utcDateOnly = Carbon::parse($date, $tz)->timezone('UTC')->toDateString();
                    $query->whereDate('created_at', '<=', $utcDateOnly);
                }, __('to_date'), 'to_date')
                    ->date()
                    ->default(request('to_date'));
            });
        });

        $grid->column('receiver', __('user'))->display(function ($name) {
            if (request()->filled('_export_')) {
                return $this?->receiver?->name ?: __('No agency');
            }
            $user = @$this->receiver ?? '';

            /** @var UserService $service */
            $service = app(UserService::class);

            return $service->adminUserAvatar($user, withoutLevels: true);
        });
        $grid->column('agency_id', __('agency'))->display(function () {
            if (request()->filled('_export_')) {
                return $this?->agency?->name ?: __('No agency');
            }
                $agency = $this->agency;
            /** @var AgencyService $agencyService */
            $agencyService = app(AgencyService::class);

            return $agencyService->adminAgencyData($agency);
        });

        $grid->column('total_gift_price', __('Total Diamond received'))->display(function ($val) {
            if (request()->filled('_export_')) {
                return "\t" . number_format($val);
            }
            return number_format($val);
        });
        $grid->disableActions();
        $grid->disableCreateButton();

        return $grid;
    }
}
