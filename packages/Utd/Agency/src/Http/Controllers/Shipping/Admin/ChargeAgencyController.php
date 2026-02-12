<?php

namespace Utd\Agency\Http\Controllers\Shipping\Admin;

use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;
use Utd\Agency\Entities\ShippingAgency;
use Utd\Agency\Traits\ResolvesExternalDependencies;

class ChargeAgencyController extends MainController
{
    use HasResourceActions;
    use ResolvesExternalDependencies;

    public $permission_name = 'charge-agency';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Verified Charging Agents'))
            ->body($this->grid()));
    }

    /**
     * Show interface.
     *
     * @param  mixed  $id
     * @return Content
     */
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('agency-country'))
            ->body($this->detail($id)));
    }

    /**
     * Edit interface.
     *
     * @param  mixed  $id
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('agency-country'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('agency-country'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new EntitiesChargeAgency());
        $countryID = session('filter_country_id');

        $grid->model()
            ->when($countryID, fn ($q) => $q->whereHas('agency', fn ($q) => $q->where('country_id', $countryID)))
            ->whereHas('agency');

        $grid->id(__('ID'));
        $grid->column('agency.name', __('Agency'))->display(function ($name) {
            if (! $this->agency) {
                return;
            }
            $cacheKey = "agency_image_{$this->agency->id}";
            $image = Cache::remember($cacheKey, 3600, function () {
                $path = @$this->agency->img;
                $defaultImage = asset('images/icon-agency.jpg');
                $url = getImagePath($path) ?? $defaultImage;

                if (! isImageExists($url)) {
                    $url = $defaultImage;
                }

                return handleShowImageWithTypes($this->agency->id, $url, 40, 40, 0);
            });

            $profileUrl = route('admin.agency.profile', ['id' => $this->agency->id]);

            return "<a href='{$profileUrl}' style='text-decoration: none; color: inherit;'>
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            {$image}
                            <div style='display: flex; flex-direction: column;'>
                                <span style='text-decoration: underline; cursor: pointer;'>{$name}</span>
                                <span style='font-size: smaller;'>ID: {$this->agency->id}</span>
                            </div>
                        </div>
                    </a>";
        });

        $this->extendGrid($grid);

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param  mixed  $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(EntitiesChargeAgency::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('agency_id', __('Agency ID'));
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
        $form = new Form(new EntitiesChargeAgency());
        $this->disableFormTools($form);

        $form->display('id', __('Id'));
        $form->select('agency_id', __('Agency'))->options(function ($value) {
            $agency = ShippingAgency::find($value);
            if ($agency) {
                return [$agency->id => $agency->name];
            }

            return [];
        })->ajax('/api/search/shipping-agencies', 'id', 'name');

        return $form;
    }
}
