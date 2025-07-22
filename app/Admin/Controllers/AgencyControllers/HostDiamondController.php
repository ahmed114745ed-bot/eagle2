<?php

namespace App\Admin\Controllers\AgencyControllers;

use App\Admin\Controllers\MainController;
use App\Admin\Services\AgencyService;
use App\Admin\Services\UserService;
use App\Models\GiftLog;
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

        return parent::index($content
            ->title(trans('Host Diamond'))
            ->body($this->grid()));
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
            ->when(! request("from_date") , fn($q) => $q->where('created_at', '>=', now()->startOfMonth()))
            ->when(! request("to_date") , fn($q) => $q->where('created_at', '<=', now()->endOfMonth()))
            ->orderByDesc('total_gift_price');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('receiver.uuid', __('uuid'));
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->equal('agency.id', __('agency id'));
            });

            $filter->where(function ($query) {
                $tz = getTimezone();

                $input = $this->input ?? now()->startOfMonth();
                $date = \App\Helpers\UserCommon::arabicToEnglishNumbers($input);

                $utcDate = \Carbon\Carbon::parse($date, $tz)->timezone('UTC')->startOfDay();

                $query->where('created_at', '>=', $utcDate);
            }, __('from_date'), 'from_date')
                ->default(request('from_date') );

            $filter->where(function ($query) {
                $tz = getTimezone();
                $input = $this->input ?? now()->endOfMonth();
                $date = \App\Helpers\UserCommon::arabicToEnglishNumbers($input);
                $utcDateOnly = \Carbon\Carbon::parse($date, $tz)->timezone('UTC')->toDateString();
                $query->whereDate('created_at', '<=', $utcDateOnly);
            }, __('to_date'), 'to_date')
                ->date()
                ->default(request('to_date') );
        });

        $grid->column('receiver', __('user'))->display(function ($name) {
            $user = @$this->receiver ?? '';

            /** @var UserService $service */
            $service = app(UserService::class);

            return $service->adminUserAvatar($user, withoutLevels: true);
        });
        $grid->column('agency_id', __('agency'))->display(function () {
            $agency = $this->agency;
            /** @var AgencyService $agencyService */
            $agencyService = app(AgencyService::class);

            return $agencyService->adminAgencyData($agency);
        });

        $grid->column('total_gift_price', __('Total Gift Price'))->display(function ($val) {
            return number_format($val);
        });
        $grid->disableActions();
        $grid->disableCreateButton();
        \Encore\Admin\Admin::script(<<<'JS'
    function arabicToEnglishNumbers(str) {
        const arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        const english = ['0','1','2','3','4','5','6','7','8','9'];
        return str.replace(/[٠-٩]/g, d => english[arabic.indexOf(d)]);
    }

    $(document).on('submit', '.form-horizontal', function () {
        const fromDateInput = $('input[name="from_date"]');
        const toDateInput = $('input[name="to_date"]');

        if (fromDateInput.length) {
            fromDateInput.val(arabicToEnglishNumbers(fromDateInput.val()));
        }

        if (toDateInput.length) {
            toDateInput.val(arabicToEnglishNumbers(toDateInput.val()));
        }
    });
JS);
        return $grid;
    }
}
