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
            ->title(trans('room_backgrounds_title'))
            ->description(trans('room_backgrounds_desc'))
            ->body($this->grid()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('room_background'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('edit_room_background'))
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
            $filter->like('room_name', trans('room_name'));
            $filter->equal('id', trans('room_id'));
            $filter->equal('numid', trans('room_numid'));
        });

        $grid->column('id', trans('ID'))->sortable();
        $grid->column('numid', trans('room_numid'));
        $grid->column('room_name', trans('room_name'))->limit(20);

        $grid->column('room_cover', trans('room_cover'))->display(function ($cover) {
            if (empty($cover)) {
                return '<span style="color:#999;">' . trans('no_cover') . '</span>';
            }
            $url = getImagePath($cover);
            return "<img src='{$url}' style='width:50px; height:50px; border-radius:4px; cursor:pointer; object-fit:cover;' onclick='openBgModal(\"{$url}\")' />";
        });

        $grid->column('final_room_image', trans('background'))->display(function () {
            $customBg = DB::table('request_background_images')
                ->where('room_id', $this->id)
                ->where('status', 1)
                ->where(function ($q) {
                    $q->where('expair', '>=', now()->timestamp)->orWhere('expair', 0);
                })
                ->orderByDesc('id')
                ->first();

            $bgImg = $customBg->img ?? $this->background?->img ?? null;
            $label = $customBg
                ? '<span class="label label-warning">' . trans('custom') . '</span>'
                : ($this->room_background ? '<span class="label label-info">' . trans('preset') . '</span>' : '<span class="label label-default">' . trans('default') . '</span>');

            if (empty($bgImg)) {
                return $label;
            }

            $url = getImagePath($bgImg);
            return "<img src='{$url}' style='width:50px; height:50px; border-radius:4px; cursor:pointer; object-fit:cover;' onclick='openBgModal(\"{$url}\")' /><br>{$label}";
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->header(function () {
            return "
                <div id='bgModalOverlay' style='display:none; position:fixed; z-index:10000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); text-align:center; cursor:pointer;' onclick='closeBgModal()'>
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
        $show->field('room_name', trans('room_name'));
        $show->field('room_cover', trans('room_cover'));
        $show->field('room_background', trans('background_id'));
        return $show;
    }

    protected function form()
    {
        $form = new Form(new Room);
        $this->disableFormTools($form);

        $roomId = request()->route('room_background_manager');
        $room = $roomId ? Room::find($roomId) : null;

        $customBg = null;
        if ($room) {
            $customBg = DB::table('request_background_images')
                ->where('room_id', $room->id)
                ->where('status', 1)
                ->where(function ($q) {
                    $q->where('expair', '>=', now()->timestamp)->orWhere('expair', 0);
                })
                ->orderByDesc('id')
                ->first();
        }

        $form->display('id', 'ID');
        $form->display('room_name', trans('room_name'));

        if ($room) {
            $form->divider(trans('current_state'));
            $form->html($this->buildPreviewHtml($room, $customBg), '');
            $form->divider(trans('edit'));
        }

        $form->image('room_cover', trans('room_cover'))
            ->disk('gcs')
            ->dir('rooms')
            ->uniqueName()
            ->removable()
            ->help(trans('upload_cover_help'));

        $form->select('room_background', trans('preset_background'))
            ->options(function () {
                $options = ['' => '-- ' . trans('default') . ' --'];
                $backgrounds = Background::where('enable', 1)->get();
                foreach ($backgrounds as $bg) {
                    $options[$bg->id] = trans('background') . " #{$bg->id}";
                }
                return $options;
            })
            ->help(trans('preset_bg_help'));

        if ($customBg) {
            $form->radio('_remove_custom_bg', trans('custom_background'))
                ->options([
                    0 => trans('keep_custom_bg'),
                    1 => trans('remove_custom_bg'),
                ])
                ->default(0)
                ->help(trans('custom_bg_help'));
        }

        $form->saving(function (Form $form) {
            if (request()->input('_remove_custom_bg') === '1') {
                DB::table('request_background_images')
                    ->where('room_id', $form->model()->id)
                    ->where('status', 1)
                    ->update(['status' => 0]);
            }
            $form->ignore('_remove_custom_bg');
        });

        return $form;
    }

    private function buildPreviewHtml($room, $customBg)
    {
        $coverUrl = $room->room_cover ? getImagePath($room->room_cover) : null;
        $presetBg = $room->background;

        $coverCard = $this->previewCard(trans('room_cover'), $coverUrl);

        if ($customBg) {
            $customUrl = getImagePath($customBg->img);
            $bgCard = $this->previewCard(
                trans('custom_background'),
                $customUrl,
                'border-color:#f0ad4e;',
                "<span class='label label-warning' style='font-size:11px;'>" . trans('active_overrides_preset') . "</span>"
            );
        } elseif ($presetBg) {
            $presetUrl = getImagePath($presetBg->img);
            $bgCard = $this->previewCard(
                trans('preset_background') . " #{$presetBg->id}",
                $presetUrl,
                'border-color:#5bc0de;',
                "<span class='label label-info' style='font-size:11px;'>" . trans('active') . "</span>"
            );
        } else {
            $bgCard = $this->previewCard(
                trans('background'),
                null,
                '',
                "<span class='label label-default' style='font-size:11px;'>" . trans('using_default') . "</span>"
            );
        }

        return "<div style='display:flex; gap:24px; flex-wrap:wrap;'>{$coverCard}{$bgCard}</div>";
    }

    private function previewCard($title, $imgUrl, $style = '', $badge = '')
    {
        $noImage = trans('no_image');
        $img = $imgUrl
            ? "<img src='{$imgUrl}' style='width:100%; max-width:220px; border-radius:6px; cursor:pointer;' onclick='openBgModal(\"{$imgUrl}\")' />"
            : "<div style='width:220px; height:140px; background:#f5f5f5; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#aaa;'>{$noImage}</div>";

        return "
            <div style='border:2px solid #ddd; border-radius:8px; padding:12px; text-align:center; min-width:240px; {$style}'>
                <div style='font-weight:600; margin-bottom:8px; font-size:13px;'>{$title}</div>
                {$img}
                <div style='margin-top:8px;'>{$badge}</div>
            </div>
        ";
    }
}
