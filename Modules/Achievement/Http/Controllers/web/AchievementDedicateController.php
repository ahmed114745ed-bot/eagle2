<?php

namespace Modules\Achievement\Http\Controllers\web;

use Carbon\Carbon;
use Encore\Admin\Grid;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use App\Models\AchievementValidImage;
use App\Admin\Controllers\MainController;
use Modules\Achievement\Entities\CustomAchievement;
use Modules\Achievement\Entities\UserAchievementLevel;

class AchievementDedicateController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    public $permission_name = 'gift-a-medal';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Gift a Badge'))
            ->body($this->grid()));
    }

    public function create(Content $content)
    {
        $achievementValidImage = AchievementValidImage::get();
        return parent::create($content
            ->title(trans('user-achievement-levels'))
            ->body(view('admin.grid.users.UserAchievementLevelDedicate', compact('achievementValidImage'))));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserAchievementLevel());
        $countryID = session('filter_country_id');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->where(function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('uuid', request('uuid'));
                });
            }, __('uuid'), 'uuid');
            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    if ($from = request('from_date')) {
                    }
                }, __('From Date'), 'from_date')->date();

                $filter->where(function ($query) {
                    if ($to = request('to_date')) {
                    }
                }, __('To Date'), 'to_date')->date();
            });
        });
        $grid->model()->with([
            'user.profile',
            'user',
            'user.packs' => fn($q) => $q->whereIn('type', [25])->where('is_used', true)->with('ware:id,value'),
            'admin',
            'customAchievement',
            'customAchievement.images' => fn($q) => $q->where('language', app()->getLocale())->select('id', 'achievement_id', 'image')
        ])
            ->when($countryID, fn($q) => $q->whereHas('user', fn($q) => $q->where('country_id', $countryID)))
            ->when(
                request('from_date') && request('to_date'),
                function ($q) {
                    $start = Carbon::parse(convertArabicToEnglishNumbers(request('from_date')))->startOfDay();
                    $end = Carbon::parse(convertArabicToEnglishNumbers(request('to_date')))->endOfDay();
                    $q->whereBetween('created_at', [$start, $end]);
                }
            )
            ->when(
                request('uuid'),
                function ($q) {
                    $q->whereHas('user', function ($u) {
                        $u->where('uuid', request('uuid'));
                    });
                }
            )
            ->where(function ($q) {
                $q->whereNotNull('custom_image')
                    ->orWhereNotNull('file')->orWhereNotNull('custom_achievement_id');
            })
            ->orderByDesc('id');

        $grid->column('id', __('Id'));
        $grid->column('user.name', __('user'))
            ->display(function ($recever) {

                $name =  $this->user?->name ?? '';

                $uid = @$this->user?->uuid ?? 0;
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

                return "
             <div style='display: flex; align-items: center; gap: 10px;'>
                 $image
                 <div>
                     <strong>$name</strong><br>
                     <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                 </div>
             </div>
         ";
            });


        $grid->column('admin.name', __('creator'))->display(function () {

            $name = $this->admin->name ?? '';
            $id = $this->admin->id ?? 0;
            if (request()->filled('_export_')) {
                return "{$name} (ID: {$id})";
            }
            $path = @$this->admin->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = $path ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            // Validate admin existence before accessing id
            $showUrl = '#'; // Default to prevent broken links
            if ($this->admin && $this->admin->id) {
                $showUrl = url("admin/auth/users/{$this->admin->id}");
            }

            return "
             <div style='display: flex; align-items: center; gap: 10px;'>
                 <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                     $image
                     <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                 </a>
             </div>
             ";
        });
        if (!request()->filled('_export_')) {
            $grid->column('file', __('image'))->display(function ($img) {
                $defaultImage = asset("images/background_room.jpg");
                $path = getImagePath($img ?? $this->custom_image ?? $this->customAchievement?->images?->firstWhere('language', app()->getLocale())?->image);
                if (!isImageExists($path)) {
                    $path = $defaultImage;
                }
                $parsedUrl = parse_url($path);
                $correctUrl = isset($parsedUrl['host']) ? $path : url("/$path");

                return "
                    <img src='$correctUrl' style='width: 50px; height: 50px; border-radius: 5px; cursor: pointer;' onclick='openModal(\"$correctUrl\")' />

                    <div id='imageModal' class='modal' style='display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.7); text-align:center;'>
                        <span onclick='closeModal()' style='position:absolute; top:10px; right:20px; font-size:30px; color:white; cursor:pointer;'>&times;</span>
                        <img id='modalImage' style='display:block; margin:auto; max-width:90%; max-height:90%; margin-top:50px; border-radius:5px;' />
                    </div>

                    <script>
                        function openModal(src) {
                            let modal = document.getElementById('imageModal');
                            let modalImage = document.getElementById('modalImage');
                            modal.style.display = 'block';
                            modalImage.src = src;
                        }

                        function closeModal() {
                            document.getElementById('imageModal').style.display = 'none';
                        }

                        // Close modal when clicking outside the image
                        document.getElementById('imageModal').addEventListener('click', function(event) {
                            if (event.target === this) {
                                closeModal();
                            }
                        });
                    </script>
                ";
            });
            $states = [
                'off' => ['value' => 0, 'text' => 'no', 'color' => 'danger'],
                'on' => ['value' => 1, 'text' => 'yes', 'color' => 'success'],
            ];
            if (Admin::user()->can('edit-' . $this->permission_name) || Admin::user()->can('*')) {
                $grid->column('is_enable', __('is enabled'))->switch($states);
            }
        } else {
            $grid->column('is_enable', __('is enabled'))->display(function ($isEnable) {
                return   $isEnable == 1 ? __('on') : __('off');
            });
        }


        $grid->column('created_at', trans('admin.created_at'));


        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
        });
        $this->extendGrid($grid);


        return $grid;
    }




    public function searchAchievement(Request $request)
    {
        $key = $request->search;
        $perPage = 10;
        $currentPage = request()->has('page') ? request()->page : 1;
        $language = app()->getLocale();
        $achievement = CustomAchievement::query()
            ->join('custom_achievement_images', function ($join) use ($language) {
                $join->on('custom_achievement_images.achievement_id', '=', 'custom_achievements.id')
                    ->where('custom_achievement_images.language', $language);
            })
            ->where(function ($query) use ($key) {
                $query->where('custom_achievements.name', 'like', '%' . $key . '%')
                    ->orWhere('custom_achievements.id', 'like', '%' . $key . '%');
            })
            ->when(isset($family), function ($query) {
                $query->where(function ($query) {
                    $query->where('users.family_id', null)->orWhere('users.family_id', 0);
                });
            })
            ->select([
                'custom_achievements.id',
                DB::raw('concat(custom_achievements.name) as name'),
                'custom_achievement_images.image',
            ])
            ->paginate($perPage, ['*'], 'page', $currentPage);
        return response()->json($achievement);
    }
}
