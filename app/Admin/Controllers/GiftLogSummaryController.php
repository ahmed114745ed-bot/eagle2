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
        $filter = request()->input('filter', 'rooms');

        return parent::index($content
            ->title(__('Gift Summary'))
            ->description($filter === 'monthly_ranking' ? __('Monthly Ranking') : __('Rooms - Agencies - Users without agency'))
            ->row(function ($row) use ($filter) {
                $row->column(12, $this->buildTabs($filter));
                if ($filter === 'monthly_ranking') {
                    $row->column(12, $this->buildRankingSubTabs());
                }
                $row->column(12, $this->grid());
            }));
    }

    protected function grid()
    {
        $grid = new Grid(new GiftLog());
        $filter = request()->input('filter', 'rooms');

        $this->applyModelFilter($grid, $filter);
        $this->addColumns($grid, $filter);

        // Standard filter for all tabs
        $grid->filter(function (Grid\Filter $gridFilter) {
                $gridFilter->expand();

                // Disable default ID filter to reorder it
                $gridFilter->disableIdFilter();

                // From Date (column 1) - start date
                $gridFilter->column(1 / 3, function ($gridFilter) {
                    $gridFilter->where(function ($query) {
                        if ($this->input) {
                            $timezone = getTimezone();
                            $start = Carbon::parse(convertArabicToEnglishNumbers($this->input), $timezone)
                                ->setTimezone('UTC');
                            $query->where('created_at', '>=', $start);
                        }
                    }, __('From Date'), 'from_date')->datetime();
                });

                // To Date (column 2) - end date
                $gridFilter->column(1 / 3, function ($gridFilter) {
                    $gridFilter->where(function ($query) {
                        if ($this->input) {
                            $timezone = getTimezone();
                            $end = Carbon::parse(convertArabicToEnglishNumbers($this->input), $timezone)
                                ->setTimezone('UTC');
                            $query->where('created_at', '<=', $end);
                        }
                    }, __('To Date'), 'to_date')->datetime();
                });

                // Room filter (only for rooms tab) - column 3
                if (request('filter') === 'rooms') {
                    $gridFilter->column(1 / 3, function ($gridFilter) {
                        $gridFilter->equal('room_id', __('room'))
                            ->select()
                            ->ajax(route('admin.filter-rooms'));
                    });
                }

                // ID filter at the end (last column)
                $gridFilter->column(1 / 3, function ($gridFilter) {
                    $gridFilter->equal('id', __('ID'))->placeholder(__('ID'));
                });
            });

        // Add custom table & filter styling
        \Encore\Admin\Facades\Admin::style('
            /* ===== Modern Table Styling ===== */
            .box {
                border-radius: 16px !important;
                border: none !important;
                box-shadow: 0 4px 24px rgba(0,0,0,0.06) !important;
                overflow: hidden !important;
            }
            .box-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                padding: 18px 24px !important;
                border: none !important;
            }
            .box-header .box-title {
                color: #fff !important;
                font-weight: 700 !important;
                font-size: 16px !important;
                letter-spacing: 0.3px !important;
            }
            .box-header .btn-group .btn {
                background: rgba(255,255,255,0.15) !important;
                border: 1px solid rgba(255,255,255,0.25) !important;
                color: #fff !important;
                border-radius: 8px !important;
                backdrop-filter: blur(4px) !important;
            }
            .box-header .btn-group .btn:hover {
                background: rgba(255,255,255,0.25) !important;
            }

            /* Table */
            .table {
                border-collapse: separate !important;
                border-spacing: 0 !important;
                margin: 0 !important;
            }
            .table > thead > tr > th {
                background: linear-gradient(135deg, #f8fafc, #eef2ff) !important;
                color: #4338ca !important;
                font-weight: 700 !important;
                font-size: 12px !important;
                text-transform: uppercase !important;
                letter-spacing: 0.8px !important;
                padding: 16px 20px !important;
                border-bottom: 2px solid #c7d2fe !important;
                border-top: none !important;
                white-space: nowrap !important;
            }
            .table > thead > tr > th:first-child {
                padding-left: 24px !important;
            }
            .table > tbody > tr {
                transition: all 0.2s ease !important;
            }
            .table > tbody > tr:hover {
                background: linear-gradient(135deg, #eef2ff, #f5f3ff) !important;
                transform: scale(1.002) !important;
            }
            .table > tbody > tr > td {
                padding: 14px 20px !important;
                border-bottom: 1px solid #f1f5f9 !important;
                border-top: none !important;
                vertical-align: middle !important;
                font-size: 13px !important;
                color: #334155 !important;
            }
            .table > tbody > tr > td:first-child {
                padding-left: 24px !important;
            }
            .table > tbody > tr:last-child > td {
                border-bottom: none !important;
            }

            /* Pagination */
            .box-footer {
                background: #f8fafc !important;
                border-top: 1px solid #e2e8f0 !important;
                padding: 12px 20px !important;
            }
            .pagination {
                margin: 0 !important;
            }
            .pagination > li > a,
            .pagination > li > span {
                border: none !important;
                border-radius: 8px !important;
                margin: 0 3px !important;
                font-weight: 600 !important;
                font-size: 13px !important;
                padding: 8px 14px !important;
                color: #6366f1 !important;
                background: #f1f5f9 !important;
                transition: all 0.2s !important;
            }
            .pagination > .active > a,
            .pagination > .active > span {
                background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
                color: #fff !important;
                box-shadow: 0 4px 12px rgba(99,102,241,0.3) !important;
            }
            .pagination > li > a:hover {
                background: #e0e7ff !important;
                color: #4338ca !important;
            }

            /* Grid tools */
            .grid-row-actions, .column-selector {
                border-radius: 8px !important;
            }

            /* Empty state */
            .table > tbody > tr > td[colspan] {
                text-align: center !important;
                padding: 48px 24px !important;
                color: #94a3b8 !important;
                font-size: 15px !important;
            }

            /* Filter styling */
            .filter-box {
                border: 1px solid #e2e8f0 !important;
                border-radius: 14px !important;
                box-shadow: 0 2px 12px rgba(0,0,0,0.04) !important;
                padding: 20px !important;
                background: #fff !important;
            }
            .filter-box .form-group {
                margin-bottom: 15px !important;
            }
            .filter-box label {
                font-weight: 600 !important;
                margin-bottom: 8px !important;
                color: #475569 !important;
                font-size: 12px !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
            }
            .filter-box .form-control {
                border: 1.5px solid #e2e8f0 !important;
                border-radius: 10px !important;
                padding: 10px 14px !important;
                font-size: 13px !important;
                transition: all 0.2s !important;
            }
            .filter-box .form-control:focus {
                border-color: #6366f1 !important;
                box-shadow: 0 0 0 3px rgba(99,102,241,0.1) !important;
            }
            .filter-box .select2-container--default .select2-selection--single {
                border: 1.5px solid #e2e8f0 !important;
                border-radius: 10px !important;
                height: 40px !important;
            }
            .filter-box .btn-primary {
                background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
                border: none !important;
                border-radius: 10px !important;
                padding: 10px 24px !important;
                font-weight: 600 !important;
                box-shadow: 0 4px 12px rgba(99,102,241,0.25) !important;
                transition: all 0.2s !important;
            }
            .filter-box .btn-primary:hover {
                transform: translateY(-1px) !important;
                box-shadow: 0 6px 16px rgba(99,102,241,0.35) !important;
            }
            .filter-box .btn-default {
                border: 1.5px solid #e2e8f0 !important;
                border-radius: 10px !important;
                padding: 10px 24px !important;
                font-weight: 600 !important;
                color: #64748b !important;
                transition: all 0.2s !important;
            }
            .filter-box .btn-default:hover {
                background: #f1f5f9 !important;
                border-color: #cbd5e1 !important;
            }

            /* Nav tabs */
            .nav-tabs-custom {
                border-radius: 14px !important;
                box-shadow: 0 2px 12px rgba(0,0,0,0.04) !important;
                border: none !important;
                overflow: hidden !important;
            }
            .nav-tabs-custom > .nav-tabs {
                border-bottom: 2px solid #e2e8f0 !important;
                background: #fff !important;
                padding: 4px 8px 0 !important;
            }
            .nav-tabs-custom > .nav-tabs > li > a {
                border: none !important;
                border-radius: 10px 10px 0 0 !important;
                padding: 12px 22px !important;
                font-weight: 600 !important;
                font-size: 13px !important;
                color: #64748b !important;
                margin-right: 4px !important;
                transition: all 0.2s !important;
            }
            .nav-tabs-custom > .nav-tabs > li > a:hover {
                background: #f1f5f9 !important;
                color: #4338ca !important;
            }
            .nav-tabs-custom > .nav-tabs > li.active > a {
                background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
                color: #fff !important;
                border: none !important;
                box-shadow: 0 -2px 12px rgba(99,102,241,0.2) !important;
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
            })
            ->when($filter === 'monthly_ranking', function ($query) {
                $rankingType = request()->input('ranking_type', 'receiver');

                $column = $rankingType === 'sender' ? 'sender_id' : 'receiver_id';
                $relation = $rankingType === 'sender' ? 'sender' : 'receiver';

                // Default to current month if no date filter is applied
                $hasDateFilter = collect(request()->all())->filter(function ($value, $key) {
                    return !empty($value) && (in_array($key, ['to_date', 'from_date']) || preg_match('/^[a-f0-9]{32}$/', $key));
                })->isNotEmpty();

                if (!$hasDateFilter) {
                    $startOfMonth = Carbon::now()->startOfMonth()->toDateTimeString();
                    $endOfMonth = Carbon::now()->endOfMonth()->toDateTimeString();
                    $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                }

                $query->select($column, DB::raw('SUM(giftPrice) as total'))
                    ->whereHas($relation, function ($q) {})
                    ->groupBy($column)
                    ->orderByDesc('total');
            });
    }

    protected function addColumns(Grid $grid, string $filter): void
    {
        $grid->column('name', __('Name'))->display(function () use ($filter) {
            return self::renderEntityCard($this, $filter);
        });


        $grid->column('total', __('diamonds'))
            ->display(function () {
                $image = asset('images/diamond.jpg');
                return "<div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($this->total) . "</span>
                    <img src='{$image}' alt='💎' width='20' height='20'>
                </div>";
            });

        if ($filter === 'rooms') {
            $grid->column('visitors_count', __('visitors'))
                ->display(function () {
                    $query = $this->room->totalRoomGifts();

                    // Laravel-Admin hashes filter keys using md5, so we need to
                    // check both readable keys and hashed keys from the request.
                    $toDate = request()->input('to_date');
                    $fromDate = request()->input('from_date');

                    // If readable keys not found, try to find hashed keys
                    if (!$toDate || !$fromDate) {
                        $allInputs = request()->all();
                        foreach ($allInputs as $key => $value) {
                            if (!$value || !is_string($value)) continue;
                            if (!$toDate && preg_match('/^[a-f0-9]{32}$/', $key)) {
                                // We can't reliably distinguish which hash is which
                                // so we skip hashed keys here - the grid filter
                                // handles date filtering via $this->input internally
                            }
                        }
                    }

                    $timezone = getTimezone();

                    if ($toDate) {
                        $end = Carbon::parse(convertArabicToEnglishNumbers($toDate), $timezone)
                            ->setTimezone('UTC');
                        $query->where('created_at', '<=', $end);
                    }

                    if ($fromDate) {
                        $start = Carbon::parse(convertArabicToEnglishNumbers($fromDate), $timezone)
                            ->setTimezone('UTC');
                        $query->where('created_at', '>=', $start);
                    }

                    return number_format($query->sum('number_of_visitors'));
                });
        }
    }

    protected function buildTabs(string $active): string
    {
        $tabs = [
            'rooms'           => __('Rooms'),
            'agencies'        => __('Agencies'),
            'users_no_agency' => __('User without agency'),
            'monthly_ranking' => __('Monthly Ranking'),
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

    protected function buildRankingSubTabs(): string
    {
        $subTab = request()->input('ranking_type', 'receiver');

        $receiverActive = $subTab === 'receiver'
            ? 'background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;box-shadow:0 4px 12px rgba(99,102,241,0.3);border:none;'
            : 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;';
        $senderActive = $subTab === 'sender'
            ? 'background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;box-shadow:0 4px 12px rgba(245,158,11,0.3);border:none;'
            : 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;';

        $receiverUrl = request()->fullUrlWithQuery(['filter' => 'monthly_ranking', 'ranking_type' => 'receiver']);
        $senderUrl = request()->fullUrlWithQuery(['filter' => 'monthly_ranking', 'ranking_type' => 'sender']);

        $receiverLabel = __('Receiver');
        $senderLabel = __('Sender');

        return <<<HTML
        <div style="background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.06);padding:16px 24px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
            <div style="display:flex;gap:8px;">
                <a href="{$receiverUrl}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border-radius:10px;font-weight:700;font-size:13px;text-decoration:none;transition:all 0.2s;{$receiverActive}">
                    <i class="fas fa-download" style="font-size:12px;"></i> {$receiverLabel}
                </a>
                <a href="{$senderUrl}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border-radius:10px;font-weight:700;font-size:13px;text-decoration:none;transition:all 0.2s;{$senderActive}">
                    <i class="fas fa-upload" style="font-size:12px;"></i> {$senderLabel}
                </a>
            </div>
        </div>
        HTML;
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

        if ($filter === 'monthly_ranking') {
            $rankingType = request()->input('ranking_type', 'receiver');
            $user = $rankingType === 'sender' ? $row->sender : $row->receiver;
            if ($user) {
                return self::entityDisplay($user->id, $user->name, $user->profile?->avatar, 'businessman-icon.jpg', url("admin/users/{$user->id}"));
            }
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
