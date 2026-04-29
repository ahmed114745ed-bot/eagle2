<?php

namespace App\Admin\Controllers;

use App\Admin\Services\UserService;
use App\Helpers\UserLevelHelper;
use App\Models\User;
use App\Models\UserCodeInvitation;
use App\Models\UserEarnInvitation;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class ParentUsersController extends MainController
{
    public $permission_name = 'user-parent';

    public function index(Content $content)
    {
        if (request("name") != null) {
            if (request("name") == 'users') {
                $user = User::select("id", "name")->find(request("ids"));
                $title = 'المستخدمين التابعين لل مستخدم : ' . $user?->name;
            } elseif (request("name") == 'operations') {
                $user = User::with(['profile', 'country', 'senderLevel', 'receiverLevel'])
                    ->find(request("useer_operation_id"));
                $title = 'العمليات التابعه لل مستخدم : ' . $user?->name;
            }
        }

        $content->title(__('Invitation code'));

        // Show user profile card for operations page
        if (request("name") == 'operations' && isset($user) && $user) {
            $content->row($this->buildUserProfileCard($user));
        }

        $content->row(function ($row) {
            $row->column(12, $this->grid());
        });

        return parent::index($content);
    }

    protected function grid()
    {
        $name = "parents";
        if (request("name") != null) {
            $name = request("name");
        }

        $grid = $name;
        $grid = $this->{$grid}();
        $grid->disableexport();
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        return $grid;
    }



    protected function parents()
    {
        $grid = new Grid(new User());
        $countryID = session('filter_country_id');
        $grid->model()->with([
            'userCodeInvite',
            'profile',
            'country',
            'senderLevel',
            'receiverLevel',
        ])->when($countryID, function ($query) use ($countryID) {
            $query->where(function ($q) use ($countryID) {
                $q->where('country_id', $countryID);
            });
        })->orderByDesc('created_at')
            ->has('codeInvitations');

        // Custom styles for enhanced user cards
        Admin::style('
            .parent-user-card {
                display: flex;
                align-items: center;
                gap: 14px;
                padding: 10px 14px;
                border-radius: 12px;
                background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
                border: 1px solid #e8ecf3;
                transition: all 0.3s ease;
                text-decoration: none;
                color: inherit;
            }
            .parent-user-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0,0,0,0.08);
                border-color: var(--primary-color, #667eea);
                text-decoration: none;
                color: inherit;
            }
            .parent-user-avatar {
                position: relative;
                flex-shrink: 0;
            }
            .parent-user-avatar img.avatar-img {
                width: 52px;
                height: 52px;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid #e0e5f0;
                transition: border-color 0.3s;
            }
            .parent-user-card:hover .parent-user-avatar img.avatar-img {
                border-color: var(--primary-color, #667eea);
            }
            .parent-user-info {
                flex: 1;
                min-width: 0;
            }
            .parent-user-name-row {
                display: flex;
                align-items: center;
                gap: 6px;
                flex-wrap: wrap;
            }
            .parent-user-name {
                font-weight: 700;
                font-size: 15px;
                color: #1a1d29;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 180px;
            }
            .parent-user-flag {
                width: 20px;
                height: auto;
                vertical-align: middle;
                border-radius: 2px;
            }
            .parent-user-meta {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-top: 3px;
                flex-wrap: wrap;
            }
            .parent-user-uid {
                font-size: 12px;
                color: #8892a6;
                background: #f0f2f8;
                padding: 2px 8px;
                border-radius: 6px;
                font-family: monospace;
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }
            .parent-user-uid .copy-btn {
                background: none;
                border: none;
                cursor: pointer;
                font-size: 11px;
                color: var(--primary-color, #667eea);
                padding: 0;
                line-height: 1;
            }
            .parent-user-uid .copy-btn:hover {
                transform: scale(1.2);
            }
            .parent-user-levels {
                display: flex;
                gap: 3px;
                margin-top: 4px;
            }
            .parent-user-levels img {
                width: 34px;
                height: 15px;
            }
            .parent-stat-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 13px;
                font-weight: 600;
            }
            .stat-users {
                background: linear-gradient(135deg, #e8f4fd, #d0e8ff);
                color: #1a6fb5;
            }
            .stat-earn {
                background: linear-gradient(135deg, #e6f9ed, #c8f5d6);
                color: #1a8a4a;
            }
            .stat-users i, .stat-earn i {
                font-size: 11px;
            }
            .action-btn-view {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 6px 16px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: 600;
                background: linear-gradient(135deg, var(--primary-color, #667eea), #764ba2);
                color: #fff !important;
                border: none;
                transition: all 0.25s ease;
                text-decoration: none;
            }
            .action-btn-view:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(102,126,234,0.4);
                color: #fff !important;
                text-decoration: none;
            }
        ');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();

            $filter->column('1/2', function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->where('uuid', $input);
                }, __('User'))->placeholder(__('Search by  UUID '));
            });
        });

        $grid->column('name', __('name'))->display(function () {
            $defaultImage = asset('images/businessman-icon.jpg');
            $path = $this->profile?->avatar;
            $url = getImagePath($path) ?? $defaultImage;
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $rawName = preg_replace('/[\x00-\x1F\x7F]/u', '', $this->name ?? '');
            $name = e($rawName);
            $uid = e($this->original_uuid);
            $special = e($this->uuid);
            $userId = $this->id;

            // Country flag
            $flagHtml = '';
            if (!empty($this->country?->flag)) {
                $flagPath = getImagePath($this->country->flag);
                $flagTitle = app()->getLocale() === 'ar'
                    ? e($this->country->name)
                    : e($this->country->e_name);
                $flagHtml = "<img src='{$flagPath}' class='parent-user-flag' alt='flag' title='{$flagTitle}'>";
            }

            // Level badges
            $levelHtml = '';
            $receiverImg = getImagePath(UserLevelHelper::getReceiverImage($this));
            $senderImg = getImagePath(UserLevelHelper::getSenderImage($this));
            if ($receiverImg || $senderImg) {
                $levelHtml = '<div class="parent-user-levels">';
                if ($receiverImg) $levelHtml .= "<img src='{$receiverImg}' title='" . __('Receiver Level') . "'>";
                if ($senderImg) $levelHtml .= "<img src='{$senderImg}' title='" . __('Sender Level') . "'>";
                $levelHtml .= '</div>';
            }

            // Profile URL
            $showUrl = url((\Encore\Admin\Facades\Admin::user()->type ?? '') === 'superadmin'
                ? "superadmin/users/profile/{$userId}"
                : "admin/users/{$userId}");

            return <<<HTML
            <a href="{$showUrl}" class="parent-user-card">
                <div class="parent-user-avatar">
                    <img src="{$url}" class="avatar-img" alt="{$name}">
                </div>
                <div class="parent-user-info">
                    <div class="parent-user-name-row">
                        <span class="parent-user-name">{$name}</span>
                        {$flagHtml}
                    </div>
                    <div class="parent-user-meta">
                        <span class="parent-user-uid">
                            UID: <span id="uid-{$userId}">{$uid}</span>
                            <button class="copy-btn" onclick="event.preventDefault();event.stopPropagation();copyToClipboard('uid-{$userId}')" title="Copy">📋</button>
                        </span>
                        <span class="parent-user-uid">Special: {$special}</span>
                    </div>
                    {$levelHtml}
                </div>
            </a>
            HTML;
        });

        $grid->column('user_count', __("user_count"))->display(function () {
            $count = count($this->userCodeInvite);
            return "<span class='parent-stat-badge stat-users'><i class='fa fa-users'></i> {$count}</span>";
        });

        $grid->column('earn', __("user_earn"))->display(function () {
            $earn = number_format($this->userCodeInvite->sum("user_percentage"), 2);
            return "<span class='parent-stat-badge stat-earn'>🪙 {$earn}</span>";
        });

        $grid->column('created_at', __('created'))->sortable()->diffForHumans();

        $grid->column('action', __('action'))->display(function () {
            return '<a href="?name=users&ids=' . $this->id . '" class="action-btn-view"><i class="fa fa-eye"></i> ' . __("users") . '</a>';
        });

        return $grid;
    }

    protected function users()
    {
        $userId = request("ids");

        $grid = new Grid(new UserCodeInvitation());

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->disableIdFilter();

            $filter->column('1/2', function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('invited', function ($q) use ($input) {
                        $q->where('uuid', $input);
                    });
                }, __('User'))->placeholder(__('Search by  UUID '));
            });
        });

        $grid->model()->with([
            'invited',
            'invited.profile',
            'invited.country',
            'invited.senderLevel',
            'invited.receiverLevel',
        ])->orderByDesc('created_at')
            ->where("user_id", $userId);

        // Styles for invited user cards
        Admin::style('
            .invited-user-card {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 8px 12px;
                border-radius: 10px;
                background: linear-gradient(135deg, #f8f9fc 0%, #fff 100%);
                border: 1px solid #e8ecf3;
                transition: all 0.3s ease;
                text-decoration: none;
                color: inherit;
            }
            .invited-user-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 16px rgba(0,0,0,0.07);
                border-color: var(--primary-color, #667eea);
                text-decoration: none;
                color: inherit;
            }
            .invited-avatar {
                flex-shrink: 0;
            }
            .invited-avatar img {
                width: 46px;
                height: 46px;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid #e0e5f0;
                transition: border-color 0.3s;
            }
            .invited-user-card:hover .invited-avatar img {
                border-color: var(--primary-color, #667eea);
            }
            .invited-info {
                flex: 1;
                min-width: 0;
            }
            .invited-name-row {
                display: flex;
                align-items: center;
                gap: 6px;
            }
            .invited-name {
                font-weight: 700;
                font-size: 14px;
                color: #1a1d29;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 160px;
            }
            .invited-flag {
                width: 18px;
                height: auto;
                border-radius: 2px;
            }
            .invited-meta {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-top: 2px;
                flex-wrap: wrap;
            }
            .invited-uid {
                font-size: 11px;
                color: #8892a6;
                background: #f0f2f8;
                padding: 1px 7px;
                border-radius: 5px;
                font-family: monospace;
                display: inline-flex;
                align-items: center;
                gap: 3px;
            }
            .invited-uid .copy-btn-sm {
                background: none;
                border: none;
                cursor: pointer;
                font-size: 10px;
                color: var(--primary-color, #667eea);
                padding: 0;
            }
            .invited-levels {
                display: flex;
                gap: 2px;
                margin-top: 3px;
            }
            .invited-levels img {
                width: 30px;
                height: 13px;
            }
            .earn-badge {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 3px 10px;
                border-radius: 16px;
                font-size: 12px;
                font-weight: 600;
                background: linear-gradient(135deg, #e6f9ed, #c8f5d6);
                color: #1a8a4a;
            }
            .earn-badge i {
                font-size: 10px;
            }
            .ops-btn {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 5px 14px;
                border-radius: 8px;
                font-size: 12px;
                font-weight: 600;
                background: linear-gradient(135deg, var(--primary-color, #667eea), #764ba2);
                color: #fff !important;
                border: none;
                transition: all 0.25s ease;
                text-decoration: none;
            }
            .ops-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 3px 10px rgba(102,126,234,0.35);
                color: #fff !important;
                text-decoration: none;
            }
        ');

        $grid->column('invited', __('name'))->display(function () {
            $user = $this->invited;
            if (!$user) return '<span class="text-muted">—</span>';

            $defaultImage = asset('images/businessman-icon.jpg');
            $path = $user->profile?->avatar ?? $user->profile?->image;
            $url = getImagePath($path) ?? $defaultImage;
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $rawName = preg_replace('/[\x00-\x1F\x7F]/u', '', $user->name ?? '');
            $name = e($rawName);
            $uid = e($user->original_uuid);
            $special = e($user->uuid);
            $uId = $user->id;

            // Country flag
            $flagHtml = '';
            if (!empty($user->country?->flag)) {
                $flagPath = getImagePath($user->country->flag);
                $flagTitle = app()->getLocale() === 'ar'
                    ? e($user->country->name)
                    : e($user->country->e_name);
                $flagHtml = "<img src='{$flagPath}' class='invited-flag' alt='flag' title='{$flagTitle}'>";
            }

            // Level badges
            $levelHtml = '';
            $receiverImg = getImagePath(UserLevelHelper::getReceiverImage($user));
            $senderImg = getImagePath(UserLevelHelper::getSenderImage($user));
            if ($receiverImg || $senderImg) {
                $levelHtml = '<div class="invited-levels">';
                if ($receiverImg) $levelHtml .= "<img src='{$receiverImg}' title='" . __('Receiver Level') . "'>";
                if ($senderImg) $levelHtml .= "<img src='{$senderImg}' title='" . __('Sender Level') . "'>";
                $levelHtml .= '</div>';
            }

            // Profile URL
            $showUrl = url((Admin::user()->type ?? '') === 'superadmin'
                ? "superadmin/users/profile/{$uId}"
                : "admin/users/{$uId}");

            return <<<HTML
            <a href="{$showUrl}" class="invited-user-card">
                <div class="invited-avatar">
                    <img src="{$url}" alt="{$name}">
                </div>
                <div class="invited-info">
                    <div class="invited-name-row">
                        <span class="invited-name">{$name}</span>
                        {$flagHtml}
                    </div>
                    <div class="invited-meta">
                        <span class="invited-uid">
                            UID: <span id="iuid-{$uId}">{$uid}</span>
                            <button class="copy-btn-sm" onclick="event.preventDefault();event.stopPropagation();copyToClipboard('iuid-{$uId}')" title="Copy">📋</button>
                        </span>
                        <span class="invited-uid">Special: {$special}</span>
                    </div>
                    {$levelHtml}
                </div>
            </a>
            HTML;
        });

        $grid->column('user_percentage', __("user_earn"))->display(function ($val) {
            $earn = number_format($val, 2);
            return "<span class='earn-badge'>🪙 {$earn}</span>";
        });

        $grid->column('created_at', __('created'))->sortable()->diffForHumans();

        $grid->column('action', __('action'))->display(function () {
            return '<a href="?name=operations&useer_operation_id=' . $this->invited_id . '" class="ops-btn"><i class="fa fa-list"></i> ' . __("Operation log") . '</a>';
        });

        return $grid;
    }

    /**
     * Build a centered profile card for the invited user
     */
    protected function buildUserProfileCard(User $user): string
    {
        $defaultImage = asset('images/businessman-icon.jpg');
        $path = $user->profile?->avatar;
        $url = getImagePath($path) ?? $defaultImage;
        if (!isImageExists($url)) {
            $url = $defaultImage;
        }

        $name = e(preg_replace('/[\x00-\x1F\x7F]/u', '', $user->name ?? ''));
        $uid = e($user->original_uuid);
        $special = e($user->uuid);
        $userId = $user->id;

        // Country
        $flagHtml = '';
        $countryName = '';
        if (!empty($user->country?->flag)) {
            $flagPath = getImagePath($user->country->flag);
            $countryName = app()->getLocale() === 'ar' ? e($user->country->name) : e($user->country->e_name);
            $flagHtml = "<img src='{$flagPath}' style='width:22px;border-radius:3px;' alt='flag'>";
        }

        // Levels
        $levelHtml = '';
        $senderImg = getImagePath(UserLevelHelper::getSenderImage($user));
        $receiverImg = getImagePath(UserLevelHelper::getReceiverImage($user));
        if ($senderImg || $receiverImg) {
            $levelHtml = '<div style="display:flex;gap:8px;justify-content:center;margin-top:10px;">';
            if ($senderImg) $levelHtml .= "<div style='text-align:center'><img src='{$senderImg}' style='width:55px;'><div style='font-size:10px;color:#8892a6;'>" . __('Sender') . "</div></div>";
            if ($receiverImg) $levelHtml .= "<div style='text-align:center'><img src='{$receiverImg}' style='width:55px;'><div style='font-size:10px;color:#8892a6;'>" . __('Receiver') . "</div></div>";
            $levelHtml .= '</div>';
        }

        // Stats
        $totalCharge = number_format(UserCodeInvitation::where('invited_id', $userId)->sum('invited_charge'), 2);
        $totalEarn = number_format(UserCodeInvitation::where('invited_id', $userId)->sum('user_percentage'), 2);
        $opsCount = UserCodeInvitation::where('invited_id', $userId)->count();

        $profileUrl = url((Admin::user()->type ?? '') === 'superadmin'
            ? "superadmin/users/profile/{$userId}"
            : "admin/users/{$userId}");

        return <<<HTML
        <style>
            .pc-wrapper { display:flex; justify-content:center; margin-bottom:20px; }
            .pc-card {
                background: linear-gradient(135deg, #fff 0%, #f8f9fc 100%);
                border: 1px solid #e0e5f0;
                border-radius: 16px;
                padding: 24px 32px;
                max-width: 460px;
                width: 100%;
                text-align: center;
                box-shadow: 0 4px 20px rgba(0,0,0,0.05);
                position: relative;
                overflow: hidden;
            }
            .pc-card::before {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0;
                height: 4px;
                background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
            }
            .pc-avatar {
                width: 76px; height: 76px;
                border-radius: 50%;
                object-fit: cover;
                border: 3px solid #e0e5f0;
                margin: 0 auto 10px;
                display: block;
                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            }
            .pc-name { font-size: 19px; font-weight: 800; color: #1a1d29; margin-bottom: 4px; }
            .pc-country { display:inline-flex; align-items:center; gap:5px; font-size:13px; color:#6b7694; margin-bottom:8px; }
            .pc-ids { display:flex; justify-content:center; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
            .pc-id {
                font-size: 11px; color: #8892a6; background: #f0f2f8;
                padding: 2px 9px; border-radius: 6px; font-family: monospace;
                display: inline-flex; align-items: center; gap: 3px;
            }
            .pc-id .pc-copy { background:none; border:none; cursor:pointer; font-size:10px; color:#667eea; padding:0; }
            .pc-stats { display:flex; justify-content:center; gap:10px; margin-top:14px; flex-wrap:wrap; }
            .pc-stat {
                background: #f8f9fc; border: 1px solid #e8ecf3;
                border-radius: 12px; padding: 8px 16px; text-align: center; min-width: 90px;
            }
            .pc-stat-val { font-size: 16px; font-weight: 800; color: #1a1d29; }
            .pc-stat-lbl { font-size: 10px; color: #8892a6; margin-top: 2px; }
            .pc-stat-charge .pc-stat-val { color: #e65100; }
            .pc-stat-earn .pc-stat-val { color: #1a8a4a; }
            .pc-stat-count .pc-stat-val { color: #1a6fb5; }
            .pc-link {
                display: inline-flex; align-items: center; gap: 5px;
                margin-top: 14px; padding: 5px 18px; border-radius: 8px;
                font-size: 12px; font-weight: 600;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: #fff !important; text-decoration: none; transition: all 0.25s;
            }
            .pc-link:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(102,126,234,0.4); color:#fff !important; text-decoration:none; }
        </style>
        <div class="pc-wrapper">
            <div class="pc-card">
                <img src="{$url}" class="pc-avatar" alt="{$name}">
                <div class="pc-name">{$name}</div>
                <div class="pc-country">{$flagHtml} {$countryName}</div>
                <div class="pc-ids">
                    <span class="pc-id">UID: <span id="pc-uid-{$userId}">{$uid}</span> <button class="pc-copy" onclick="copyToClipboard('pc-uid-{$userId}')" title="Copy">📋</button></span>
                    <span class="pc-id">Special: {$special}</span>
                </div>
                {$levelHtml}
                <div class="pc-stats">
                    <div class="pc-stat pc-stat-count">
                        <div class="pc-stat-val">{$opsCount}</div>
                        <div class="pc-stat-lbl"><i class="fa fa-list"></i> Operations</div>
                    </div>
                    <div class="pc-stat pc-stat-charge">
                        <div class="pc-stat-val">💎 {$totalCharge}</div>
                        <div class="pc-stat-lbl">Total Charge</div>
                    </div>
                    <div class="pc-stat pc-stat-earn">
                        <div class="pc-stat-val">🪙 {$totalEarn}</div>
                        <div class="pc-stat-lbl">Parent Earn</div>
                    </div>
                </div>
                <a href="{$profileUrl}" class="pc-link"><i class="fa fa-user"></i> View Profile</a>
            </div>
        </div>
        HTML;
    }

    protected function operations()
    {
        $userId = request("useer_operation_id");

        $grid = new Grid(new UserCodeInvitation());
        $grid->model()->orderByDesc('created_at')
            ->where("invited_id", $userId);

        // Styles for operations grid
        Admin::style('
            .ops-id-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 36px;
                padding: 3px 10px;
                border-radius: 8px;
                font-size: 12px;
                font-weight: 700;
                background: #f0f2f8;
                color: #5a6178;
                font-family: monospace;
            }
            .ops-charge-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 4px 14px;
                border-radius: 20px;
                font-size: 13px;
                font-weight: 600;
                background: linear-gradient(135deg, #fff3e0, #ffe0b2);
                color: #e65100;
            }
            .ops-earn-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 4px 14px;
                border-radius: 20px;
                font-size: 13px;
                font-weight: 600;
                background: linear-gradient(135deg, #e6f9ed, #c8f5d6);
                color: #1a8a4a;
            }
            .ops-date-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 3px 10px;
                border-radius: 8px;
                font-size: 12px;
                color: #6b7694;
                background: #f5f6fa;
            }
        ');

        $grid->column('id', __('ID'))->display(function ($val) {
            return "<span class='ops-id-badge'>#{$val}</span>";
        });

        $grid->column('invited_charge', __("user_charge"))->display(function ($val) {
            $charge = number_format($val, 2);
            return "<span class='ops-charge-badge'>🪙 {$charge}</span>";
        });

        $grid->column('user_percentage', __("parent_earn"))->display(function ($val) {
            $earn = number_format($val, 2);
            return "<span class='ops-earn-badge'>🪙 {$earn}</span>";
        });

        $grid->column('created_at', __('created'))->sortable()->display(function ($val) {
            $diff = \Carbon\Carbon::parse($val)->diffForHumans();
            return "<span class='ops-date-badge'><i class='fa fa-clock-o'></i> {$diff}</span>";
        });

        return $grid;
    }
}
