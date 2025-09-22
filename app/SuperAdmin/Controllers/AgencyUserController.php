<?php

namespace App\SuperAdmin\Controllers;

use App\Admin\Actions\CanPlaySwitchAction;
use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Layout\Content;
use App\Admin\Actions\ChangeAgencyAction;
use App\Admin\Actions\ChargeSwitchAction;
use App\Admin\Actions\InviteSwitchAction;
use App\Admin\Actions\KickOfAgencyAction;
use App\Admin\Actions\KickOfFamilyAction;
use App\Admin\Controllers\MainController;
use App\Admin\Selectable\ImageColors;
use App\Facades\UserHandling;
use App\Models\Country;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\SwitchAccount\Entities\UserAccount;
use Modules\Achievement\Http\Services\UserAchievementService;
use Session;

class AgencyUserController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;
    public $permission_name = 'hosts';

    public function __construct()
    {
        $this->title = 'Hosts';
    }


    public function index(Content $content)
    {
        $content = $content->title(__($this->title));

        $content = $content->row(function ($row) {
            $row->column(12, $this->grid());
        })->row(view('admin.same_device_users_modal'));

        return $content;
    }

    public function indexProfessionals(Content $content)
    {
        $content = $content->title(__($this->title));

        $content = $content->row(function ($row) {
            $row->column(12, $this->gridProfessional());
        })->row(view('admin.same_device_users_modal'));

        return $content;
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $haveCoins = (request()->have_coins == 1);
        $grid->model()->ofAgency()
            ->with([ 'profile', 'packs'])
            ->where('is_host', 1)
            ->where('country_id', auth()->user()->country_id)
            ->withCount('sameDeviceUsers');
        $grid->quickSearch();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('agency_id', __('agency'))->select(Common::by_agency_filter());

                $filter->column(1 / 2, function ($filter) {
                    $filter->where(function ($query) {
                        $input = $this->input;
                        $query->where('name', 'like', "%$input%")
                            ->orWhere('uuid', 'like', "%$input%")->orWhere('special_id', 'like', "%$input%")->orWhere('nickname', 'like', "%$input%")->orWhere('email', 'like', "%$input%");
                    }, __('User'))->placeholder(__('Search by name , UUID , nickname and email'));
                });
            });
        });
        $grid->column('id', __('Id'));
        if ($haveCoins) {
            $grid->column('di', __('coins'))->display(function ($value) {
                return number_format($value);
            });
        }

        $grid->column('name', __('Name'))
            ->display(function ($name) {
                if (request()->filled('_export_')) {
                    return $this->name;
                }
                $uid = @$this->uuid;
                $path = @$this->profile?->avatar;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;
                $receiver_img = @$this->getImageReceiverOrSender('receiver_id', 1)?->img ?? '';
                $receiverImg = getImagePath($receiver_img) ?? $defaultImage;

                $sender_img = @$this->getImageReceiverOrSender('sender_id', 2)?->img ?? '';
                $senderImg = getImagePath($sender_img) ?? $defaultImage;

                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }
                $image = handleShowImageWithTypes($this->id, $url, 50, 50);
                $showUrl = url("superadmin/users/profile/{$this->id}");

                return "
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                            $image
                            <div>
                                <strong>$name</strong><br>
                                <span style='font-size: smaller;'>UID: $uid</span><br>
                                <img src='$receiverImg' style='width: 20px; height: 20px; border-radius: 50%;'>
                                <span style='font-size: smaller;'>Receiver Level</span><br>
                                <img src='$senderImg' style='width: 20px; height: 20px; border-radius: 50%;'>
                                <span style='font-size: smaller;'>Sender Level</span>
                            </div>
                        </div>
                        ";
            });

        $grid->column('agency', __('Agency'))
            ->display(function () {
                if (request()->filled('_export_')) {
                    return $this?->agency?->name ?: __('No agency');
                }
                if (!$this->agency) {
                    return "<span style='color: #aaa;'>No agency</span>";
                }

                $name = $this->agency->name ?? '';

                $cacheKey = "agency_image_{$this->agency_id}";
                $image = Cache::remember($cacheKey, 3600, function () {
                    $path = $this->agency->img;
                    $defaultImage = asset("images/icon-agency.jpg");
                    $url = getImagePath($path) ?? $defaultImage;

                    if (!isImageExists($url)) {
                        $url = $defaultImage;
                    }

                    return handleShowImageWithTypes($this->agency_id, $url, 40, 40);
                });

                $profileUrl = route('superadmin.agency.profile', ['id' => $this->agency_id]);

                return "
                    <a href='{$profileUrl}' style='text-decoration: none; color: inherit;'>
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            {$image}
                            <div style='display: flex; flex-direction: column;'>
                                    <span style='text-decoration: underline; cursor: pointer;'>{$name}</span>
                                <span style='font-size: smaller;'>ID: {$this->agency_id}</span>
                            </div>
                        </div>
                    </a>
                ";
            });

        Admin::style('.btn-circle {width: 30px; height: 30px; font-size:15px; border-radius: 50%; text-align: center; }');
        Admin::style('tr{background-color:var(--table-background-color);}.btn-circle {width: 30px; height: 30px; font-size:15px; border-radius: 50%; text-align: center; }');
        Admin::style("
            .modal-dialog {
                max-width: 90%;
            }

            .modal {
                top: 5%;
            }

            .modal-body {
                max-height: 70vh !important;
                overflow-y: auto !important;
            }
        ");

        $grid->column('custom_button2', __('عدد الحسابات'))->display(function () {
            $count = $this->same_device_users_count;
            return "<button class='btn btn-sm btn-primary show-same-device-modal' data-user-id='{$this->id}'>$count</button>";
        });

        Admin::script("
            $(document).on('click', '.show-same-device-modal', function() {
                console.log('here');
                var userId = $(this).data('user-id');
                $('#sameDeviceUsersModal .modal-body').html('Loading...');
                $('#sameDeviceUsersModal').modal('show');
                $.get('/superadmin/users/' + userId + '/same-device-users-table', function(html) {
                    $('#sameDeviceUsersModal .modal-body').html(html);
                });
            });
        ");

        $grid->disableActions();

        $grid->disableCreateButton();

        return $grid;
    }

    protected function gridProfessional()
    {
        $grid = new Grid(new User());
        $haveCoins = (request()->have_coins == 1);
        $grid->model()
            ->ofAgency()
            ->with(['profile', 'packs', 'agency.country', 'country'])
            ->where('is_host', 1)
            ->withCount('sameDeviceUsers')
            ->where(function ($query) {
                $currentCountry = auth()->user()->country_id;
                $query->where(function ($q) use ($currentCountry) {
                    $q->where('country_id', $currentCountry)
                        ->whereHas('agency', function ($a) use ($currentCountry) {
                            $a->where('country_id', '!=', $currentCountry);
                        });
                })->orWhere(function ($q) use ($currentCountry) {
                    $q->where('country_id', '!=', $currentCountry)
                        ->whereHas('agency', function ($a) use ($currentCountry) {
                            $a->where('country_id', $currentCountry);
                        });
                });
            });

        $grid->quickSearch();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('agency_id', __('agency'))->select(Common::by_agency_filter());

                $filter->column(1 / 2, function ($filter) {
                    $filter->where(function ($query) {
                        $input = $this->input;
                        $query->where('name', 'like', "%$input%")
                            ->orWhere('uuid', 'like', "%$input%")->orWhere('special_id', 'like', "%$input%")->orWhere('nickname', 'like', "%$input%")->orWhere('email', 'like', "%$input%");
                    }, __('User'))->placeholder(__('Search by name , UUID , nickname and email'));
                });
            });
        });
        $grid->column('id', __('Id'));
        if ($haveCoins) {
            $grid->column('di', __('coins'))->display(function ($value) {
                return number_format($value);
            });
        }

        $grid->column('name', __('Name'))
            ->display(function ($name) {
                if (request()->filled('_export_')) {
                    return $this->name;
                }
                $uid = @$this->uuid;
                $path = @$this->profile?->avatar;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;

                $receiver_img = @$this->getImageReceiverOrSender('receiver_id', 1)?->img ?? '';
                $receiverImg = getImagePath($receiver_img) ?? $defaultImage;

                $sender_img = @$this->getImageReceiverOrSender('sender_id', 2)?->img ?? '';
                $senderImg = getImagePath($sender_img) ?? $defaultImage;

                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }
                $image = handleShowImageWithTypes($this->id, $url, 50, 50);
                $showUrl = url("superadmin/users/profile/{$this->id}");
                $country = app()->getLocale() == 'ar' ? $this->country?->name : $this->country?->e_name;

                return "
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                            $image
                            <div>
                                <strong>$name</strong><br>
                                <span style='font-size: smaller;'>UID: $uid</span><br>
                                <span style='font-size: smaller;'>Country: $country</span><br>
                                <img src='$receiverImg' style='width: 20px; height: 20px; border-radius: 50%;'>
                                <span style='font-size: smaller;'>Receiver Level</span><br>
                                <img src='$senderImg' style='width: 20px; height: 20px; border-radius: 50%;'>
                                <span style='font-size: smaller;'>Sender Level</span>
                            </div>
                        </div>
                        ";
            });

        $grid->column('agency', __('Agency'))
            ->display(function () {
                if (request()->filled('_export_')) {
                    return $this?->agency?->name ?: __('No agency');
                }
                if (!$this->agency) {
                    return "<span style='color: #aaa;'>No agency</span>";
                }

                $name = $this->agency->name ?? '';

                $cacheKey = "agency_image_{$this->agency_id}";
                $image = Cache::remember($cacheKey, 3600, function () {
                    $path = $this->agency->img;
                    $defaultImage = asset("images/icon-agency.jpg");
                    $url = getImagePath($path) ?? $defaultImage;

                    if (!isImageExists($url)) {
                        $url = $defaultImage;
                    }

                    return handleShowImageWithTypes($this->agency_id, $url, 40, 40);
                });

                $profileUrl = route('superadmin.agency.profile', ['id' => $this->agency_id]);
                $country = app()->getLocale() == 'ar' ? $this->agency->country?->name : $this->agency->country?->e_name;

                return "
                    <a href='{$profileUrl}' style='text-decoration: none; color: inherit;'>
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            {$image}
                            <div style='display: flex; flex-direction: column;'>
                                    <span style='text-decoration: underline; cursor: pointer;'>{$name}</span>
                                <span style='font-size: smaller;'>ID: {$this->agency_id}</span>
                                <span style='font-size: smaller;'>Country: $country</span><br>
                            </div>
                        </div>
                    </a>
                ";
            });

        Admin::style('.btn-circle {width: 30px; height: 30px; font-size:15px; border-radius: 50%; text-align: center; }');
        Admin::style('tr{background-color:var(--table-background-color);}.btn-circle {width: 30px; height: 30px; font-size:15px; border-radius: 50%; text-align: center; }');
        Admin::style("
            .modal-dialog {
                max-width: 90%;
            }

            .modal {
                top: 5%;
            }

            .modal-body {
                max-height: 70vh !important;
                overflow-y: auto !important;
            }
        ");

        $grid->column('custom_button2', __('عدد الحسابات'))->display(function () {
            $count = $this->same_device_users_count;
            return "<button class='btn btn-sm btn-primary show-same-device-modal' data-user-id='{$this->id}'>$count</button>";
        });

        Admin::script("
            $(document).on('click', '.show-same-device-modal', function() {
                console.log('here');
                var userId = $(this).data('user-id');
                $('#sameDeviceUsersModal .modal-body').html('Loading...');
                $('#sameDeviceUsersModal').modal('show');
                $.get('/superadmin/users/' + userId + '/same-device-users-table', function(html) {
                    $('#sameDeviceUsersModal .modal-body').html(html);
                });
            });
        ");

        $grid->disableActions();

        $grid->disableCreateButton();

        return $grid;
    }

}
