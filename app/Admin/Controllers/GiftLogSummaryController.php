<?php
namespace App\Admin\Controllers;

use App\Models\GiftLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Grid;

class GiftLogSummaryController extends AdminController
{
    public function index(Content $content)
    {
        return $content
            ->title(__('Gift Summary'))
            ->description(__('Rooms - Agencies - Users without agency'))
            ->body($this->grid());
    }
    
    protected function grid()
    {
        $grid = new Grid(new GiftLog());
        $filter = request()->input('filter', 'rooms');
    
        $this->applyModelFilter($grid, $filter);
        $this->addColumns($grid, $filter);
    
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->between('created_at', __('Date and Time'))->datetime();
        });
    
        $grid->header(function () use ($filter) {
            return $this->buildTabs($filter);
        });
    
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableExport();
        $grid->disableRowSelector();
    
        return $grid;
    }
    
    protected function applyModelFilter(Grid $grid, string $filter): void
    {
        $grid->model()
        ->when($filter === 'rooms', function ($query) {
            $query->select('room_id', DB::raw('SUM(giftPrice) as total'))
                ->whereNotNull('room_id')
                ->whereHas('room', function ($q) {
                    $q->whereNull('deleted_at');
                })
                ->groupBy('room_id');
        })
        ->when($filter === 'agencies', function ($query) {
            $query->select('agency_id', DB::raw('SUM(giftPrice) as total'))
                ->whereNotNull('agency_id')
                ->whereHas('agency', function ($q) {
                    $q->whereNull('deleted_at');
                })
                ->groupBy('agency_id');
        })
        ->when($filter === 'users_no_agency', function ($query) {
            $query->select('receiver_id', DB::raw('SUM(giftPrice) as total'))
                ->whereNull('agency_id')
                ->whereHas('receiver', function ($q) {
                    $q->whereNull('deleted_at');
                })
                ->groupBy('receiver_id');
        });
    }
    
    protected function addColumns(Grid $grid, string $filter): void
    {
        $grid->column('name', __('Name'))->display(function () use ($filter) {
            return self::renderEntityCard($this, $filter);
        });
    
        $grid->column('total', __('Total Gifts'));
    }
    
    protected function buildTabs(string $active): string
    {
        $tabs = [
            'rooms'           => __('Rooms'),
            'agencies'        => __('Agencies'),
            'users_no_agency' => __('User without agency'),
        ];
    
        $html = '<div class="nav-tabs-custom" style="margin-bottom:20px; z-index: -4;position: absolute;"><ul class="nav nav-tabs">';
        foreach ($tabs as $key => $label) {
            $isActive = $key === $active ? 'active' : '';
            $url = request()->fullUrlWithQuery(['filter' => $key]);
            $html .= "<li class='{$isActive}'><a href='{$url}' class='tab-link'>{$label}</a></li>";
        }
        $html .= '</ul></div>';
    
        $html .= <<<JS
            <style>
                .nav-tabs-custom { z-index: 0; position: relative; }
                .tab-content, .filters { position: relative; z-index: 9; }
            </style>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('.tab-link').forEach(tab => {
                        tab.addEventListener('click', function(e) {
                            e.preventDefault();
                            window.location.href = tab.getAttribute('href');
                        });
                    });
                });
            </script>
        JS;
    
        return $html;
    }
    
    protected static function renderEntityCard($row, string $filter): string
    {
        if ($filter === 'agencies' && $agency = $row->agency) {
            return self::entityDisplay($agency->id, $agency->name, $agency->img, 'icon-agency.jpg', route('admin.agency.profile', $agency->id));
        }
    
        if ($filter === 'rooms' && $room = $row->room) {
            return self::entityDisplay($room->id, $room->room_name, $room->room_cover, 'room.jpg', url("admin/rooms/{$room->id}"));
        }
    
        if ($filter === 'users_no_agency' && $user = $row->receiver) {
            return self::entityDisplay($user->id, $user->name, $user->profile?->avatar, 'businessman-icon.jpg', url("admin/users/{$user->id}"));
        }
    
        return '';
    }
    
    protected static function entityDisplay($id, $name, $path, $default, $url): string
    {
        $cacheKey = "entity_image_{$id}";
        $image = Cache::remember($cacheKey, 3600, function () use ($path, $default, $id) {
            $url = getImagePath($path) ?? asset("images/{$default}");
            if (!isImageExists($url)) $url = asset("images/{$default}");
            return handleShowImageWithTypes($id, $url, 40, 40, 0);
        });
    
        return "<a href='{$url}' style='text-decoration: none; color: inherit;'>
                    <div style='display: flex; align-items: center; gap: 10px;'>
                        {$image}
                        <div style='display: flex; flex-direction: column;'>
                            <span style='text-decoration: underline; cursor: pointer;'>{$name}</span>
                            <span style='font-size: smaller;'>ID: {$id}</span>
                        </div>
                    </div>
                </a>";
    }
    
    }
    

