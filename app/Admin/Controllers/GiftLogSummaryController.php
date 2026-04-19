<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use Encore\Admin\Grid;
use App\Models\GiftLog;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GiftLogSummaryController extends MainController
{
    public $permission_name = 'gift-logs';
    public function index(Content $content)
    {

        return parent::index($content
            ->title(__('Gift Summary'))
            ->description(__('Rooms - Agencies - Users without agency'))
            ->row(function ($row) {
                $row->column(12, $this->buildTabs(request()->input('filter', 'rooms')));
                $row->column(12, $this->grid());
            }));
    }

    protected function grid()
    {
        $grid = new Grid(new GiftLog());
        $filter = request()->input('filter', 'rooms');

        $this->applyModelFilter($grid, $filter);
        $this->addColumns($grid, $filter);

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();

            // Disable default ID filter to reorder it
            $filter->disableIdFilter();

            // Date range filters - From Date (column 1)
            $filter->column(1 / 3, function ($filter) {
                $filter->where(function ($query) {
                    if ($this->input) {
                        $timezone = getTimezone();
                        $end = Carbon::parse(convertArabicToEnglishNumbers($this->input), $timezone)
                            ->setTimezone('UTC');
                        $query->where('created_at', '<=', $end);
                    }
                }, __('To Date'), 'to_date')->datetime();
            });

            // To Date (column 2)
            $filter->column(1 / 3, function ($filter) {
                $filter->where(function ($query) {
                    if ($this->input) {
                        $timezone = getTimezone();
                        $start = Carbon::parse(convertArabicToEnglishNumbers($this->input), $timezone)
                            ->setTimezone('UTC');
                        $query->where('created_at', '>=', $start);
                    }
                }, __('From Date'), 'from_date')->datetime();
            });

            // Room filter (only for rooms tab) - column 3
            if (request('filter') === 'rooms') {
                $filter->column(1 / 3, function ($filter) {
                    $filter->equal('room_id', __('room'))
                        ->select()
                        ->ajax(route('admin.filter-rooms'));
                });
            }

            // ID filter at the end (last column)
            $filter->column(1 / 3, function ($filter) {
                $filter->equal('id', __('ID'))->placeholder(__('ID'));
            });
        });

        // Add custom filter styling
        \Encore\Admin\Facades\Admin::style('
            .filter-box {
                border: 1px solid var(--gray-600) !important;
                border-radius: var(--border-radius) !important;
                box-shadow: var(--shadow-md) !important;
                padding: 20px !important;
            }
            .filter-box .form-group {
                margin-bottom: 15px !important;
            }
            .filter-box label {
                font-weight: 600 !important;
                margin-bottom: 8px !important;
            }
            .filter-box .form-control {
                border: 1px solid var(--gray-300) !important;
                border-radius: var(--border-radius) !important;
            }
            .filter-box .select2-container--default .select2-selection--single {
                border: 1px solid var(--gray-300) !important;
                border-radius: var(--border-radius) !important;
            }
            .filter-box .btn-primary {
                border: none !important;
                border-radius: var(--border-radius) !important;
            }
            .filter-box .btn-default {
                border: none !important;
                border-radius: var(--border-radius) !important;
            }
        ');



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
                    ->whereHas('room', function ($q) {})
                    ->groupBy('room_id');
            })
            ->when($filter === 'agencies', function ($query) {
                $query->select('agency_id', DB::raw('SUM(giftPrice) as total'))
                    ->whereNotNull('agency_id')
                    ->whereHas('agency', function ($q) {})
                    ->groupBy('agency_id');
            })
            ->when($filter === 'users_no_agency', function ($query) {
                $query->select('receiver_id', DB::raw('SUM(giftPrice) as total'))
                    ->whereNull('agency_id')
                    ->whereHas('receiver', function ($q) {})
                    ->groupBy('receiver_id');
            });
    }

    protected function addColumns(Grid $grid, string $filter): void
    {
        $grid->column('name', __('Name'))->display(function () use ($filter) {
            return self::renderEntityCard($this, $filter);
        });

        $grid->column('total', __('diamonds'))
            ->display(function () {
                $image = asset('images/diamond.jpg'); // Make sure this image exists
                return "<div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($this->total) . "</span>
                    <img src='{$image}' alt='💎' width='20' height='20'>
                </div>";
            });
    }

    protected function buildTabs(string $active): string
    {
        $tabs = [
            'rooms'           => __('Rooms'),
            'agencies'        => __('Agencies'),
            'users_no_agency' => __('User without agency'),
        ];

        $html = '<div class="nav-tabs-custom" style="margin-bottom:20px;"><ul class="nav nav-tabs">';
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
