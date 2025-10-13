<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Family;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\FamilyUser;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use App\Services\AppFeatureService;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;

class FamilyController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'families';
    public $hiddenColumns = [];

    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("families");
    }

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('families'))
            ->body($this->grid()));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('families'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('families'))
            ->body($this->form()));
    }

    // public function show($id, Content $content)
    // {
    //     return parent::show($id, $content
    //         ->title(trans('families'))
    //         ->body($this->detail($id)));
    // }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Family);
        $countryID = session('country_id');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->equal('id', __('ID'));
            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    $query->whereHas('owner', function ($subQuery) {
                        $subQuery->where('uuid', 'like', "%{$this->input}%");
                    });
                }, __('UUID'), 'uuid')->placeholder(__('search for host by UUID'));
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    if ($date = request('date')) {
                        $dateEn = Carbon::parse(convertArabicToEnglishNumbers($date))->endOfDay();
                        $query->whereDate('created_at',  $dateEn);
                    }
                }, __('created_at'), 'date')->date();
            });
        });
        $grid->model()
            ->when($countryID, fn($q) => $q->whereHas('owner', fn($q) => $q->where('country_id', $countryID)))
            ->with([
            'owner:id,name,uuid', // only needed fields
            'owner.profile:id,user_id,avatar',
            'owner.packs' => fn($q) => $q
                ->select('id', 'user_id', 'type', 'is_used', 'target_id')
                ->where('type', 25)
                ->where('is_used', true)
                ->with('ware:id,value'),
        ])->orderByDesc('id');

        $grid->id(__('ID'));
        $grid->column('image', __('family'))->display(function ($image) {
            $path = @$image;
            $name = $this->name;
            $defaultImage = asset("images/family.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                  <strong>$name</strong><br>
            </div>
        ";
        });

        $grid->column('owner.name', trans('owner'))->display(function ($name) {
            $uid = $this->owner?->uuid;
            $path = $this->owner?->profile?->avatar;
            $defaultImage = asset('images/businessman-icon.jpg');
            $url = getImagePath($path) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = $this->owner ? url("admin/users/{$this->owner->id}") : '#';

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

        $grid->column('num', __('number of people'))->display(function ($value) {
            return $this->members_count . '/' . $value;
        });;
        $grid->column('num_admins', __('number of admins'))->display(function ($value) {
            return $this->admins_num . '/' . $value;
        });
        $grid->column('max_level', __('level'));
        $grid->column('max_exp', __('exp'));
        $grid->column('created_at', __('created_at'));

        $grid->tools(function (Grid\Tools $tools) {
            $uuid = request('uuid') ?? (request('owner')['uuid'] ?? null);
            $query = http_build_query([

                'date' => request('date') ? convertArabicToEnglishNumbers(request('date')) : '',
                'id' => request('id'),
                'uuid' => $uuid,
            ]);

            $tools->append('<a href="' . url('/admin/families-excel') . '?' . $query . '" target="_blank" class="btn btn-sm btn-success">
                <i class="fa fa-download"></i>' . __('admin.exportExcel') . '</a>');
        });
        $this->extendGrid($grid);
        $grid->disableExport();
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    // protected function detail($id)
    // {
    //     $show = new Show(Family::findOrFail($id));

    //     $show->id(__('ID'));
    //     //        $show->is_success('is_success');
    //     $show->image(__('image'));
    //     $show->name(__('name'));
    //     $show->introduce(__('introduce'));
    //     $show->notice(__('notice'));
    //     $show->num(__('number of people'));
    //     $show->user_id(__('user id'));
    //     $show->speakswitch(__('speak switch'));
    //     $show->status(__('status'));
    //     //        $show->update_user_id('update_user_id');
    //     //        $show->suctime('suctime');
    //     //        $show->start_time('start_time');
    //     //        $show->created_at(trans('admin.created_at'));
    //     //        $show->updated_at(trans('admin.updated_at'));
    //     $this->extendShow($show);
    //     return $show;
    // }

    public function show($id, Content $content,)
    {
        $type = request('type');
        $family = Family::with('allMembers', 'owner')->find($id);
        $familyMembers = $family->allMembers()->when(isset($type), function ($query) use ($type) {
            $query->where('user_type', $type);
        })->paginate(10, ['*'], 'member_page');
        return  parent::show($id, $content->title(__('family profile'))
            ->view('family_profile', compact('family', 'familyMembers')));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Family);
        $this->disableFormTools($form);

        $form->display('ID');
        $form->text('name', __('name'))->rules('required');
        $form->text('introduce', __('introduce'))->rules('required');
        $form->text('notice', __('notice'))->rules('required');
        $form->image('image', __('image'));
        $form->text('num', __('number of people'))->rules('required|integer|max:10000')->default('20');
        $form->select('user_id', __('user id'))->options(function ($value) {
            $ops2 = [];
            foreach (User::Where('id', $value)->get() as $user) {
                $ops2[$user->id] = $user->uuid . '_' . $user->name;
            }
            return $ops2;
        })->ajax('/api/search/users4', 'id', 'name')->rules('required');
        $form->hidden('is_success', 'is_success')->default(1)->rules('required');

        $form->saving(function (Form $form) {
            $oldOwnerFamily = $form->model()->user_id;
            $newOwnerFamily = request()->user_id;
            if ($form->model()->exists && ($oldOwnerFamily != $newOwnerFamily)) {
                User::where('id', $form->model()->user_id)->update(['family_id' => 0]);
                FamilyUser::where([
                    'user_id' => $form->model()->user_id,
                    'family_id' => $form->model()->id,
                    'user_type' => 2,
                    'status' => 1,
                ])->delete();
            }
        });
        $form->saved(function (Form $form) {
            $checkFamilyUser = FamilyUser::where([
                'user_id' => $form->model()->user_id,
                'family_id' => $form->model()->id,
                'user_type' => 2,
                'status' => 1,
            ])->exists();
            if (!$checkFamilyUser) {
                User::where('id', $form->model()->user_id)->update(['family_id' => $form->model()->id]);
                FamilyUser::create([
                    'user_id' => $form->model()->user_id,
                    'family_id' => $form->model()->id,
                    'user_type' => 2,
                    'status' => 1,
                ]);
            }
        });

        return $form;
    }
}
