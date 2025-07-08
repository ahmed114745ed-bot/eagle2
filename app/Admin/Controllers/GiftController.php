<?php

namespace App\Admin\Controllers;

use App\Admin\Forms\TabsFrom;
use App\Models\Gift;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
//use Encore\Admin\Admin;
use Illuminate\Support\Str;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;

class GiftController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'gift';
    public function index(Content $content)
    {
        return parent::index($content
            ->title(__($this->title))
            // ->row(function (Row $row) {
            //     $row->column(12, $this->grid2());
            // })
            ->row(function ($row) {
                $row->column(12, $this->grid());
            }));
    }

    protected function grid2()
    {
        $make_rooms_top = settings()->get('close_open_gifts');
        return (new Box(
            title: __('admin.Actions'),
            content: view('admin.grid.users.closeOpenGifts', compact(['make_rooms_top'])),
        ));
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
            ->title(trans('Gifts'))
            ->body($this->detail($id)));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {

        if (url()->previous() != url()) {
            session(['return_url' => url()->previous()]);
        }

        return parent::edit($id, $content
            ->title(trans('Gifts'))
            ->body($this->form()->edit($id)));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Gifts'))
            ->body($this->form()));
    }



    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Gift);

        $filterType = request('filter', 'all');

        $grid->model()
            ->with('vip')
            ->where('type', '!=', 8)
            ->when($filterType !== 'all', fn($q) => $q->where('type', $filterType))
            ->orderBy('use_count', 'desc')
            ->orderBy('type')
            ->orderByRaw('ISNULL(`sort`), `sort`')
            ->orderBy('price');

        $grid->paginate(20);


        $grid->header(function () use ($filterType) {
            $tabs = ['all' => __('All')] + translate(TYPE_GIFT);
            $html = '<div class="nav-tabs-custom"><ul class="nav nav-tabs">';
            foreach ($tabs as $key => $label) {
                $active = $filterType === (string)$key ? 'active' : '';
                $url = request()->fullUrlWithQuery(['filter' => $key]);
                $html .= "<li class='{$active}'><a href='{$url}'>{$label}</a></li>";
            }
            $html .= '</ul></div>';
            return $html;
        });

        $grid->id(__('ID'));
        $grid->name(__('Name'));

        if ($filterType == 9) {
            $grid->column('level', trans('vip'))->display(function () {
                $defaultImage = asset("images/image.png");
                $path = getImagePath($this?->vip?->img);
                $url = $path ?: $defaultImage;
                return handleShowImageWithTypes($this->id, $url, 50, 50);
            });
            $grid->column('vip_level', __('level_num'));
        }

        if (Admin::user()->can('edit_gift_price') || Admin::user()->can('*')) {
            $grid->column('enable', trans('enable'))->switch(Common::getSwitchStates());
        }

        $grid->column('price', __('price'))->display(function ($coin) {
            $icon = asset('images/coin.jpg');
            return "
            <div style='display: flex; align-items: center; gap: 5px;'>
                <span>" . number_format($coin) . "</span>
                <img src='{$icon}' alt='Coin' width='20' height='20'>
            </div>
        ";
        });

        $grid->column('img', trans('image'))->display(function ($path) {
            $imgPath = getImagePath($path) ?: asset("images/image.png");
            $musicIcon = $this->music_gift == 1
                ? "<img src='" . asset('images/music.jpg') . "' 
                style='position: absolute; top: 5px; right: 5px; width: 20px; height: 20px;
                background-color: rgba(0, 0, 0, 0.5); border-radius: 50%; padding: 2px;'>"
                : '';

            return "<div style='position: relative; display: inline-block;'>
                    <img src='{$imgPath}' style='width: 70px; height: 70px;' class='img img-thumbnail' />
                    {$musicIcon}
                </div>";
        });

        $grid->column('show_img', trans('show_img'))->display(function ($path) {
            $url = getImagePath($path) ?: asset('images/image.png');
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $grid->column("use_count", __('use count'));


        $this->extendGrid($grid);

        $grid->disableExport();

        Admin::script("
        if (window.innerWidth >= 1024) {
            $('.table-responsive').removeClass('table-responsive');
        }
    ");

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Gift::findOrFail($id));
        // $show->id('ID');
        // $show->name(__('name'));
        // $show->e_name(__('e_name'));
        // $show->type(__('type'));
        // $show->vip_level(__('vip_level'));
        // $show->hot(__('hot'));
        // $show->is_play('is_play');
        // $show->price(__('price'));
        // $show->img(__('img'));
        // $show->show_img(('show_img'));
        // $show->show_img2('show_img2');
        // $show->sort('sort');
        // $show->enable('enable');
        $this->extendShow($show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new TabsFrom(new Gift);
        $this->disableFormTools($form);

        $form->display(__('ID'));
        $form->text('name', __('name'));

        $form->select('type', __('type'))->options(
            translate(TYPE_GIFT)
        )
            ->when(6, function () use ($form) {
                $form->number('luckyGift.win_probability', __('win probability'))
                    ->min(10)->max(100)
                    ->placeholder(__('Enter win probability'))
                    ->attribute(['id' => 'win_probability']);

                $probabilityTimes1 = \Cache::get('probability_times_1', []);
                $probabilityTimes2 = \Cache::get('probability_times_2', []);
                $probabilityTimes3 = \Cache::get('probability_times_3', []);

                $form->decimal('luckyGift.min_percentag', __('min percentage') . ' (%)')
                    ->help('<span id="min_percent_display">' . '[' . implode(', ', $probabilityTimes1) . '] - ' . __('scope for multiplies') .  '</span>')
                    ->rules('min:0|max:100')
                    ->default(0)
                    ->required();

                $form->decimal('luckyGift.mid_percentag', __('mid percentage') . ' (%)')
                    ->help('<span id="mid_percent_display">' . '[' . implode(', ', $probabilityTimes2) . ' ]- ' . __('scope for multiplies') .  '</span>')
                    ->rules('min:0|max:100')
                    ->default(0)
                    ->required();

                $form->decimal('luckyGift.max_percentag', __('max percentage') . ' (%)')
                    ->help('<span id="max_percent_display">' . '[' . implode(', ', $probabilityTimes3) . '] - ' . __('scope for multiplies') .  '</span>')
                    ->rules('min:0|max:100')
                    ->default(0)
                    ->required();

                $form->html(<<<'HTML'
                    <script>
                        (function () {
                            const fields = ['min_percentag', 'mid_percentag', 'max_percentag'];

                            function getVal(field) {
                                return parseFloat($(`input[name="luckyGift[${field}]"]`).val()) || 0;
                            }

                            function setVal(field, val) {
                                val = Math.max(0, Math.min(100, val));
                                $(`input[name="luckyGift[${field}]"]`).val(val.toFixed(2));
                            }

                            function updateDisplays() {
                                // $('#min_percent_display').text('🔹 النسبة الحالية: ' + getVal('min_percentag') + '%');
                                // $('#mid_percent_display').text('🔸 النسبة الحالية: ' + getVal('mid_percentag') + '%');
                                // $('#max_percent_display').text('🟣 النسبة الحالية: ' + getVal('max_percentag') + '%');
                            }

                            function enforceLimit(changed) {
                                const total = getVal('min_percentag') + getVal('mid_percentag') + getVal('max_percentag');
                                if (total > 100) {
                                    let current = getVal(changed);
                                    let overflow = total - 100;
                                    setVal(changed, current - overflow);
                                }
                            }
                            window.percent_error_message = ' . json_encode(trans('admin.percent_error')) . ';

                            function checkBeforeSubmit(e) {
                                const total = getVal('min_percentag') + getVal('mid_percentag') + getVal('max_percentag');
                                if (Math.round(total) !== 100) {
                                    alert(window.percent_error_message + total.toFixed(2) + '%');
                                    e.preventDefault();
                                    return false;
                                }
                            }

                            $(document).ready(function () {
                                fields.forEach(function(field) {
                                    $(document).on('input', `input[name="luckyGift[${field}]"]`, function () {
                                        let val = parseFloat($(this).val()) || 0;
                                        if (val < 0) val = 0;
                                        if (val > 100) val = 100;
                                        $(this).val(val.toFixed(2));

                                        enforceLimit(field);
                                        updateDisplays();
                                    });
                                });

                                $('form').on('submit', checkBeforeSubmit);
                                updateDisplays();
                            });
                        })();
                        </script>

                    HTML);
            })
            ->when(9, function () use ($form) {
                $form->number('vip_level', __('vip_level'))->min(0)->placeholder(__('less than 256'))->attribute(['id' => 'vip_level']);
            });

        $form->currency('price', __('price'))->symbol('💎');
        $form->switch('enable', __('enable'))->states(Common::getSwitchStates());

        $form->number('vip_level', __('vip_level'))->min(0)->placeholder(__('less than 256'))->attribute(['id' => 'vip_level']);

        $form->file('img', __('img'));
        $form->file('show_img', __('show_img'))->name(function ($file) {
            return 'svga_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        })->required();
        $form->select('image_type', __('image_type'))->options(
            [
                'svga' => __('svga'),
                'alpha' => __('alpha'),
                'mp4' => __('mp4'),
                'vap' => __('vap'),
            ]
        )->required();






        $form->switch('music_gift', trans('music_gift'))->states(Common::getSwitchStatesGiftMucic());
        $form->saving(function (Form $form) {



            if ($form->model()->type == "6" || request()->type == 6) {
                $type = $form->input('type');
                $win_probability = $form->input('luckyGift.win_probability');
                $min_percentag = $form->input('luckyGift.min_percentag');
                $mid_percentage = $form->input('luckyGift.mid_percentag');
                $max_percentage = $form->input('luckyGift.max_percentag');
                $total = $min_percentag + $mid_percentage + $max_percentage;
                if (($min_percentag + $mid_percentage + $max_percentage) != 100) {
                    $error = new \Illuminate\Support\MessageBag([
                        'title' => 'Error',
                        'message' => trans('admin.percent_total_error', ['total' => $total]),
                    ]);

                    return back()->with(compact('error'))->withInput();
                }
            }
        });
        return $form;
    }
}
