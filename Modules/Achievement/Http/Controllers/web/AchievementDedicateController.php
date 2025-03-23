<?php

namespace Modules\Achievement\Http\Controllers\web;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Auth;


use App\Models\AchievementValidImage;

use App\Admin\Controllers\MainController;
use App\Admin\Actions\AchievementDedicate;
use Modules\Achievement\Entities\Achievement;
use App\Admin\Actions\AchievementDedicateAction;
use Modules\Achievement\Entities\AchievementLevel;
use Modules\Achievement\Entities\UserAchievementLevel;



class AchievementDedicateController extends MainController
{
/**
     * Title for current resource.
     *
     * @var string
     */
    public $permission_name = 'achievement_dedicate';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Gift Badges'))
            ->body($this->grid()));
    }

    public function create(Content $content)
    {
        $achievementValidImage = AchievementValidImage::get();
        return parent::create($content
             ->title(trans('user-achievement-levels'))
            ->body(view('admin.grid.users.UserAchievementLevelDedicate',compact('achievementValidImage'))));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AchievementValidImage());

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
        });
        $grid->column('id', __('Id'));
        $grid->column( 'file',__('image'))->display(function ($img) {
            $defaultImage = asset("images/background_room.jpg");
            $path = getImagePath($img);
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




        $grid->column ('return',__ ('dedicate'))->display (function (){
           
            return (new AchievementDedicate($this->id))->render ();
          });
          $grid->disableActions();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
           // $actions->add(new AchievementDedicateAction());
        });


        return $grid;
    }

}
