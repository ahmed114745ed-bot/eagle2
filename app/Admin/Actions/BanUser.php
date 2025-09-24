<?php

namespace App\Admin\Actions;

use App\Models\Ban;
use App\Models\Room;
use App\Models\User;
use App\Helpers\Common;
use App\Models\BanType;
use Illuminate\Http\Request;
use Encore\Admin\Actions\Action;
use App\Facades\CustomNotification;
use Illuminate\Support\Facades\Auth;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Facades\Admin as AuthAdmin;
use Encore\Admin\Facades\Admin;


class BanUser extends Action
{
    public $name;

    protected $selector = '.ban_user_action';
    public $permission_name = 'bans';

    public function handle(Request $request)
    {
        if (!AuthAdmin::user()->can('*')) {
            Permission::check('create-' . $this->permission_name);
        }
        $user = User::query()->searchByUuid($request->uuid)->first();
        if (!$user) return $this->response()->error('user not found')->refresh();
        $userUuid  = $user->original_uuid;
        $now = now();
        $messages = [];
        $newBan = false;

        if (!$user) {
            return $this->response()->error(__('user not found'))->refresh();
        }
        $room = Room::query()->where('uid',  $user->now_room_uid)->first();
        // $ban = Ban::query()->where('uid', $userUuid)->where('ty')->first();
        if (in_array('ip', $request->type)) {

            $haveBan = Ban::query()->where('uid', $userUuid)->where('type', 'ip')->whereRaw("created_at + INTERVAL duration HOUR > '$now'")
                ->exists();
            if ($haveBan) {
                $messages[] = __('already have ip ban');
            } else {
                $newBan = true;
                $ips = $user->ips;
                foreach ($ips as $ip) {
                    // if(!$ban && $ip != $ban->ip){
                    Ban::query()->create(
                        [
//                            'uid' => $userUuid,
                            'duration' => $request->duration,
                            'ip' => $ip->ip,
                            'type' => 'ip',
                            'user_type' => 0,
                            'staff_id' => Auth::id(),
                            'description_ar' => $request->description_ar,
                            'description_en' => $request->description_en ?? $request->description_ar,
                            'img' => ($request->file('img') ? ($request->file('img') ? Common::upload('bans', $request->file('img')) : "") : "")
                        ]
                    );
                    // }
                }
            }
        }

        if (in_array('device', $request->type)) {
            $haveBan = Ban::query()->where('uid', $userUuid)->where('type', 'device')->whereRaw("created_at + INTERVAL duration HOUR > '$now'")
                ->exists();
            if ($haveBan) {
                $messages[] = __('already have device ban');
            } else {
                $newBan = true;

                Ban::query()->create(
                    [
//                        'uid' => $userUuid,
                        'duration' => $request->duration,
                        'device_number' => $user->device_token,
                        'type' => 'device',
                        'user_type' => 0,
                        'staff_id' => Auth::id(),
                        'description_ar' => $request->description_ar,
                        'description_en' => $request->description_en ?? $request->description_ar,
                        'img' => ($request->file('img') ? Common::upload('bans', $request->file('img')) : "")
                    ]
                );
            }
        }

        if (in_array('normal', $request->type)) {
            $haveBan = Ban::query()->where('uid', $userUuid)->where('type', 'normal')->whereRaw("created_at + INTERVAL duration HOUR > '$now'")
                ->exists();
            if ($haveBan) {
                $messages[] = __('already have normal ban');
            } else {
                $newBan = true;

                Ban::query()->create(
                    [
                        'uid' => $userUuid,
                        'duration' => $request->duration,
                        'type' => 'normal',
                        'user_type' => 0,
                        'staff_id' => Auth::id(),
                        'description_ar' => $request->description_ar,
                        'description_en' => $request->description_en ?: $request->description_ar,
                        'img' => ($request->file('img') ? Common::upload('bans', $request->file('img')) : ""),
                    ]
                );
            }
        }

        if ($request->ban_type_id) {
            $haveBan = Ban::query()->where('uid', $userUuid)->where('ban_type_id', $request->ban_type_id)->whereRaw("created_at + INTERVAL duration HOUR > '$now'")->exists();
            if ($haveBan)  $messages[] = __('already have ban');
            $ban = Ban::query()->create(
                [
                    'uid' => $userUuid,
                    'duration' => $request->duration,
                    'type' => 'action',
                    'user_type' => 0,
                    'staff_id' => Auth::id(),
                    'description_ar' => $request->description_ar,
                    'description_en' => $request->description_en ?: $request->description_ar,
                    'img' => ($request->file('img') ? Common::upload('bans', $request->file('img')) : ""),
                    'ban_type_id' => $request->ban_type_id,
                ]
            );

            $route = $ban->banType?->route;

            if ($route == 'rooms/enter_room' && $user->room) {
                // dd( $user->room());
                $ms = [
                    'messageContent' => [
                        "message" => "unableToEnterRoom",
                        'user_id' => $user->id,
                        'reason_ar' => $request->description_ar ?? '',
                        'reason_en' => $request->description_en ?? '',
                        'duration' => $request->duration ?? 0,
                    ]
                ];
                $json = json_encode($ms);
                Common::sendToZego('SendCustomCommand', @$user->room->id, $user->room->uid, $json);
            } else if ($route == 'rooms/up_microphone' && $user->room) {
                $ms = [
                    'messageContent' => [
                        "message" => "unableToUPMicrophone",
                        'user_id' => $user->id,
                        'reason_ar' => $request->description_ar ?? '',
                        'reason_en' => $request->description_en ?? '',
                        'duration' => $request->duration ?? 0,
                    ]
                ];
                $json = json_encode($ms);
                Common::sendToZego('SendCustomCommand', @$user->room->id, $user->room->uid, $json);
            } else if ($route == 'rooms/up-microphone' && $user->room) {
                $ms = [
                    'messageContent' => [
                        "message" => "unableToUPMicrophone",
                        'user_id' => $user->id,
                        'reason_ar' => $request->description_ar ?? '',
                        'reason_en' => $request->description_en ?? '',
                        'duration' => $request->duration ?? 0,
                    ]
                ];
                $json = json_encode($ms);
                Common::sendToZego('SendCustomCommand', @$user->room->id, $user->room->uid, $json);
            }
        }

        if ($room && $newBan) {
            $d = [
                "messageContent" => [
                    "message" => "banDevice",
                    "userId" => $user->id,
                    'reason_ar' => $request->description_ar ?? '',
                    'reason_en' => $request->description_en ?? '',
                    'duration' => $request->duration ?? 0,
                ]
            ];
            $json = json_encode($d);

            Common::sendToZego('SendCustomCommand', $room->id, $user->id, $json);
        }

        if ($newBan) {
            CustomNotification::banUser($user, $request->duration);
        }

        if ($request->filled('ban_accounts')) {
            foreach ($request->ban_accounts as $accountId) {
                $accountUser = User::find($accountId);
                if (!$accountUser) {
                    continue;
                }

                $exists = Ban::query()
                    ->where('uid', $accountUser->original_uuid)
                    ->where('type', 'normal')
                    ->whereRaw("created_at + INTERVAL duration HOUR > '$now'")
                    ->exists();

                if ($exists) {
                    $messages[] = __("User {$accountUser->name} already has a normal ban");
                } else {
                    Ban::create([
                        'uid'            => $accountUser->original_uuid,
                        'duration'       => $request->duration,
                        'type'           => 'normal',
                        'user_type'      => 0,
                        'staff_id'       => Auth::id(),
                        'description_ar' => $request->description_ar,
                        'description_en' => $request->description_en ?? $request->description_ar,
                        'img'            => ($request->file('img') ? Common::upload('bans', $request->file('img')) : ""),
                    ]);

                    CustomNotification::banUser($accountUser, $request->duration);
                }
            }
        }

        if (count($messages) > 0) {
            return $this->response()->error(implode("<br>", $messages))->refresh();
        }

        return $this->response()->success('success')->refresh();
    }

    public function form()
    {
        $this->text('uuid', __('uuid'))->rules('required');
        $this->integer('duration', __('duration(hours)'))->rules('required|max:6');
        $this->text('description_ar', __('Enter the reason for the ban(arabic)'))
            ->rules(['required', 'string', 'max:255']);

        $this->text('description_en', __('Enter the reason for the ban(english)'))
            ->rules(['nullable', 'string', 'max:255']);

        $this->image('img', __('img'))
            ->rules(['nullable', 'image', 'mimes:jpeg,png,jpg',])
            ->setValidationMessages('img', [
                'image' => __('admin.custom_img_image'),
                'mimes' => __('admin.custom_img_mimes'),
            ]);

        $this->checkbox('type', __('type'))->options([
            'normal' => __('normal'),
            'ip' => __('ip'),
            'device' => __('device'),
            'others' => __('others'),
        ]);

//        $this->multipleSelect('ban_accounts', __('Select Accounts to Ban'))->options([]);
        $this->checkbox('ban_accounts', __('Select Accounts to Ban'))->options([]);


        $this->select('ban_type_id', __('ban_type'))
            ->options(function () {
                $locale = app()->getLocale();
                if ($locale === 'ar') {
                    return BanType::all()->pluck('name_ar', 'id');
                }
                return BanType::all()->pluck('name_en', 'id');
            })
            ->attribute(['id' => 'ban_type_id_field']);

//
//        $this->select('ban_type_id', __('ban_type'))->options(function ($value) {
//            $ops2 = [];
//            foreach (BanType::get() as $ban) {
//                $ops2[$ban->id] = $ban->name_en . '_' . $ban->name_ar;
//            }
//            return $ops2;
//        });
    }

    public function html()
    {
        Admin::script(<<<JS
            $('#ban_type_id_field').closest('.form-group').hide();

            $(document).on('ifChecked', 'input[name="type[]"][value="others"]', function() {
                $('#ban_type_id_field').closest('.form-group').show();
            });

            $(document).on('ifUnchecked', 'input[name="type[]"][value="others"]', function() {
                $('#ban_type_id_field').closest('.form-group').hide();
                $('#ban_type_id_field').val('').trigger('change');
            });
        JS);

        Admin::script(<<<JS
            $(document).on('ifChecked', 'input[name="type[]"][value="device"]', function() {
                console.log("Device checkbox checked with iCheck!");
                var uuid = $('input[name="uuid"]').val();
                if (!uuid) {
                    toastr.error('Please enter UUID first');
                    return;
                }
                $.ajax({
                    url: '/api/search/user-accounts',
                    data: {uuid: uuid},
                    success: function(res) {
                        console.log("Accounts:", res);

                        var container = $('input[name="ban_accounts[]"]').closest('.form-group');
                        container.find('.dynamic-ban-accounts').remove();

                        if (res.length === 0) {
                            container.append('<div class="dynamic-ban-accounts"><p>No accounts found</p></div>');
                        } else {
                            var html = '<div class="dynamic-ban-accounts" style="margin-top:10px;">';
                            res.forEach(function(acc) {
                                var checked = (acc.uuid == uuid) ? 'checked' : '';
                                html += '<div style="margin-bottom:5px;">' +
                                            '<label style="display:block; font-weight:normal;">' +
                                                '<input type="checkbox" name="ban_accounts[]" value="' + acc.id + '" ' + checked + '> ' +
                                                (acc.name ? acc.name : "Unknown") + ' (UUID: ' + acc.uuid + ')' +
                                            '</label>' +
                                        '</div>';
                            });
                            html += '</div>';
                            container.append(html);

                            $('input[name="ban_accounts[]"]').iCheck({
                                checkboxClass: 'icheckbox_minimal-blue'
                            });

                            $('input[name="ban_accounts[]"][value]').each(function() {
                                if ($(this).is(':checked')) {
                                    $(this).iCheck('check');
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                        toastr.error('Failed to load accounts');
                    }
                });
            });
        JS);


        $banText = __('create bans');
        return <<<HTML
            <a href="javascript:void(0);" class="ban_user_action btn btn-sm text-white"
               style="background-color: var(--primary-color); border-color: var(--secondary-color);">
                {$banText}
            </a>
        HTML;
        }}
