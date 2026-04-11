<?php

namespace App\Admin\Controllers;

use App\Models\Background;
use App\Models\Room;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class RoomBackgroundManagerController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'rooms';

    public function index(Content $content)
    {
        return parent::index($content
            ->title('Room Backgrounds')
            ->description('Manage room cover images and backgrounds')
            ->body($this->grid()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title('Room Background')
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title('Edit Room Background')
            ->body($this->form()->edit($id)));
    }

    protected function grid()
    {
        $grid = new Grid(new Room);

        $grid->model()->orderByDesc('id');

        $grid->disableCreateButton();
        $grid->disableExport();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('room_name', 'Room Name');
            $filter->equal('id', 'Room ID');
            $filter->equal('numid', 'Room NumID');
        });

        $grid->column('id', 'ID')->sortable();
        $grid->column('numid', 'NumID');
        $grid->column('room_name', 'Room Name');

        $grid->column('room_cover', 'Cover')->display(function ($cover) {
            if (empty($cover)) {
                return '<span style="color:#999;">No cover</span>';
            }
            $url = getImagePath($cover);
            return "<img src='{$url}' style='width:60px; height:60px; border-radius:5px; cursor:pointer; object-fit:cover;' onclick='openBgModal(\"{$url}\")' />";
        });

        $grid->column('final_room_image', 'Background')->display(function () {
            // Check custom background first (highest priority)
            $customBg = DB::table('request_background_images')
                ->where('room_id', $this->id)
                ->where('status', 1)
                ->where(function ($q) {
                    $q->where('expair', '>=', now()->timestamp)->orWhere('expair', 0);
                })
                ->orderByDesc('id')
                ->first();

            $bgImg = $customBg->img ?? $this->background?->img ?? null;
            $source = $customBg ? 'Custom' : ($this->room_background ? 'Preset #' . $this->room_background : 'Default');

            if (empty($bgImg)) {
                return '<span style="color:#999;">Default</span>';
            }

            $url = getImagePath($bgImg);
            return "<div>
                <img src='{$url}' style='width:60px; height:60px; border-radius:5px; cursor:pointer; object-fit:cover;' onclick='openBgModal(\"{$url}\")' />
                <br><small style='color:#888;'>{$source}</small>
            </div>";
        });

        $grid->column('room_background', 'BG ID');

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->header(function () {
            return "
                <div id='bgModalOverlay' style='display:none; position:fixed; z-index:10000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.8); text-align:center; cursor:pointer;' onclick='closeBgModal()'>
                    <span style='position:absolute; top:15px; right:25px; font-size:35px; color:white; cursor:pointer;'>&times;</span>
                    <img id='bgModalImg' style='max-width:90%; max-height:90%; margin-top:50px; border-radius:8px;' />
                </div>
                <script>
                    function openBgModal(src) {
                        document.getElementById('bgModalImg').src = src;
                        document.getElementById('bgModalOverlay').style.display = 'block';
                    }
                    function closeBgModal() {
                        document.getElementById('bgModalOverlay').style.display = 'none';
                    }
                </script>
            ";
        });

        $this->extendGrid($grid);

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Room::findOrFail($id));
        $show->field('id', 'ID');
        $show->field('room_name', 'Room Name');
        $show->field('room_cover', 'Room Cover');
        $show->field('room_background', 'Background ID');
        return $show;
    }

    protected function form()
    {
        $form = new Form(new Room);
        $this->disableFormTools($form);

        $form->display('id', 'ID');
        $form->display('room_name', 'Room Name');

        // Current state preview
        $form->divider('Current State');

        $form->html(function () {
            $roomId = request()->route('room_background_manager');
            $room = Room::find($roomId);
            if (!$room) return '';

            $coverUrl = $room->room_cover ? getImagePath($room->room_cover) : null;

            // Get custom background
            $customBg = DB::table('request_background_images')
                ->where('room_id', $room->id)
                ->where('status', 1)
                ->where(function ($q) {
                    $q->where('expair', '>=', now()->timestamp)->orWhere('expair', 0);
                })
                ->orderByDesc('id')
                ->first();

            $presetBg = $room->background;
            $activeImg = $customBg->img ?? $presetBg?->img ?? null;
            $activeUrl = $activeImg ? getImagePath($activeImg) : null;
            $source = $customBg ? "Custom (request_background_images #{$customBg->id})" : ($presetBg ? "Preset Background #{$presetBg->id}" : "Default");

            $coverHtml = $coverUrl
                ? "<img src='{$coverUrl}' style='max-width:200px; max-height:200px; border-radius:8px; border:2px solid #ddd;' />"
                : '<span style="color:#999;">No cover image</span>';

            $bgHtml = $activeUrl
                ? "<img src='{$activeUrl}' style='max-width:200px; max-height:200px; border-radius:8px; border:2px solid #ddd;' />"
                : '<span style="color:#999;">Default background</span>';

            $customNote = $customBg
                ? "<div style='margin-top:10px; padding:10px; background:#fff3cd; border:1px solid #ffc107; border-radius:5px;'>
                       <strong>Note:</strong> This room has an active custom background (ID: {$customBg->id}).
                       Check the <b>'Remove Custom Background'</b> checkbox below to remove it.
                       Otherwise, changing the preset background will have no visible effect.
                   </div>"
                : '';

            return "
                <div style='display:flex; gap:30px; align-items:flex-start;'>
                    <div style='text-align:center;'>
                        <h5>Room Cover</h5>
                        {$coverHtml}
                    </div>
                    <div style='text-align:center;'>
                        <h5>Active Background ({$source})</h5>
                        {$bgHtml}
                    </div>
                </div>
                {$customNote}
            ";
        });

        $form->divider('Edit');

        $form->image('room_cover', 'Room Cover')
            ->disk('gcs')
            ->dir('rooms')
            ->uniqueName()
            ->removable();

        $form->select('room_background', 'Preset Background')
            ->options(function () {
                $options = ['' => '-- No preset --'];
                $backgrounds = Background::where('enable', 1)->get();
                foreach ($backgrounds as $bg) {
                    $options[$bg->id] = "Background #{$bg->id}";
                }
                return $options;
            });

        // Check if room has custom background
        $roomId = request()->route('room_background_manager');
        if ($roomId) {
            $hasCustom = DB::table('request_background_images')
                ->where('room_id', $roomId)
                ->where('status', 1)
                ->where(function ($q) {
                    $q->where('expair', '>=', now()->timestamp)->orWhere('expair', 0);
                })
                ->exists();

            if ($hasCustom) {
                $form->checkbox('_remove_custom_bg', 'Remove Custom Background')
                    ->options([1 => 'Yes, remove the custom background so the preset one is used instead']);
            }
        }

        $form->saving(function (Form $form) {
            // Handle custom background removal
            if (request()->input('_remove_custom_bg') && in_array('1', request()->input('_remove_custom_bg', []))) {
                DB::table('request_background_images')
                    ->where('room_id', $form->model()->id)
                    ->where('status', 1)
                    ->update(['status' => 0]);
            }

            // Remove the virtual field so it doesn't try to save to rooms table
            $form->ignore('_remove_custom_bg');
        });

        return $form;
    }
}
