<?php

namespace App\Repositories\User;

use App\Helpers\AgencyPackageHelper;
use Exception;
use Utd\Bd\Entities\Bd;
use App\Support\PackageHelper;
use App\Models\User;
use App\Models\Follow;
use App\helper\UserDataHelper;
use App\Models\ProfileGallary;
use Modules\SuperAdmin\Entities\SuperAdmin;
use App\Models\UserEarnInvitation;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;
use Modules\AreaManager\Entities\AreaManager;
use Modules\AreaManager\Entities\SubAreaManager;
use App\Http\Resources\Api\V1\UserDataRoomResource;
use Utd\Agency\Repositories\UserRepository as Repository;

class UserRepository extends Repository
{
    public function search($key, $family, $perPage, $currentPage)
    {
        return User::query()
            ->join('profiles', 'profiles.user_id', '=', 'users.id')
            ->where(function ($query) use ($key) {
                $query->where('users.name', 'like', '%' . $key . '%')
                    ->orWhere('users.uuid', 'like', '%' . $key . '%')
                    ->orWhere('users.id', 'like', '%' . $key . '%')
                    ->orWhere('users.special_id', 'like', '%' . $key . '%');
            })
            ->when(isset($family), function ($query) {
                $query->where(function ($query) {
                    $query->where('users.family_id', null)->orWhere('users.family_id', 0);
                });
            })
            ->select([
                'users.id',
                DB::raw('concat(users.name, " - ", users.uuid) as name'),
                'profiles.avatar',
            ])
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }


    public function searchWithPage($key, $page, $perPage)
    {
        return User::selectRaw('concat(name, " - ", uuid) as name, id')
            ->where('name', 'like', '%' . $key . '%')
            ->orWhere('uuid', 'like', '%' . $key . '%')
            ->orWhere('id', 'like', '%' . $key . '%')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function searchOwnerRoomWithPage($key, $page, $perPage)
    {
        return User::selectRaw('CONCAT(name, " - ", uuid) AS name, id')
            ->whereHas('ownerAudioRoom')
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', "%{$key}%")
                    ->orWhere('uuid', 'like', "%{$key}%")
                    ->orWhere('id', 'like', "%{$key}%");
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function searchAudioOwnerWithPage($key, $page, $perPage)
    {
        return User::selectRaw('concat(name, " - ", uuid) as name, id')->whereDoesntHave('ownerAudioRoom')
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->orWhere('uuid', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function searchLiveOwnerWithPage($key, $page, $perPage)
    {
        return User::selectRaw('concat(name, " - ", uuid) as name, id')->whereDoesntHave('ownerLiveRoom')
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->orWhere('uuid', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function searchWithPageNew($key, $page, $perPage)
    {

        return User::select('id', 'uuid', 'name')
            ->where('name', 'like', '%' . $key . '%')
            ->orWhere('uuid', 'like', '%' . $key . '%')
            ->orWhere('id', 'like', '%' . $key . '%')
            ->paginate($perPage, ['*'], 'page', $page);
    }


    public function searchUserAgency($key, $page, $perPage)
    {

        return User::selectRaw('CONCAT(COALESCE(name, ""), " - ", COALESCE(NULLIF(special_id, ""), uuid)) as name, id')
            ->where(function ($query) {
                $query->where('agency_id', 0)
                    ->orWhereNull('agency_id');
            })
            ->where(function ($query) {
                $query->where('is_bd', 0)
                    ->orWhereNull('is_bd');
            })
            ->whereDoesntHave('hostAgency', function ($query) {
                $query->where('type', 1);
            })
            //            ->whereDoesntHave('shippingAgency')
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->orWhere('uuid', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%')
                    ->orWhere('special_id', 'like', '%' . $key . '%');
            })

            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function bdCountryUsers($key, $page, $perPage, $country_id)
    {

        return User::selectRaw('CONCAT(COALESCE(name, ""), " - ", COALESCE(NULLIF(special_id, ""), uuid)) as name, id')
            ->where('country_id', $country_id)
            ->where(function ($query) {
                $query->where('agency_id', 0)
                    ->orWhereNull('agency_id');
            })
            ->where(function ($query) {
                $query->where('is_bd', 0)
                    ->orWhereNull('is_bd');
            })
            ->where(function ($query) {
                $query->where('is_area_manager', 0)->orWhereNull('is_area_manager');
            })->where(function ($query) {
                $query->where('sub_area_manger', 0)->orWhereNull('sub_area_manger');
            })->where(function ($query) {
                $query->where('is_super_admin', 0)->orWhereNull('is_super_admin');
            })->where(function ($query) {
                $query->where('is_sub_super_admin', 0)->orWhereNull('is_sub_super_admin');
            })
            ->whereDoesntHave('hostAgency', function ($query) {
                $query->where('type', 1);
            })
            //            ->whereDoesntHave('shippingAgency')
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->orWhere('uuid', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%')
                    ->orWhere('special_id', 'like', '%' . $key . '%');
            })

            ->paginate($perPage, ['*'], 'page', $page);
    }



    public function user_bd($key, $page, $perPage)
    {
        return User::selectRaw('concat(COALESCE(name, ""), " - ", uuid) as name, id')
            ->where(function ($query) {
                $query->where('is_bd', 0)->orWhereNull('is_bd');
            })->where(function ($query) {
                $query->where('is_area_manager', 0)->orWhereNull('is_area_manager');
            })->where(function ($query) {
                $query->where('sub_area_manger', 0)->orWhereNull('sub_area_manger');
            })->where(function ($query) {
                $query->where('is_super_admin', 0)->orWhereNull('is_super_admin');
            })->where(function ($query) {
                $query->where('is_sub_super_admin', 0)->orWhereNull('is_sub_super_admin');
            })
            ->where(function ($query) {
                $query->where('agency_id', 0)
                    ->orWhereNull('agency_id');
            })
            // ->where('type_user', 0)
            ->whereDoesntHave('hostAgency', function ($query) {
                $query->where('type', 1);
            })
            ->whereDoesntHave('shippingAgency')
            ->where(function ($query) use ($key) {
                $query->fitterByUuid($key)->orWhere('name', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function user_bd2($key, $page, $perPage)
    {
        if (!PackageHelper::isInstalled('bd')) {
            return collect();
        }
        return Bd::selectRaw('concat(COALESCE(username, ""), " - ", id) as name, id')
            ->where(function ($query) use ($key) {

                $query->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function userBdByCountries($areaManagerId, $key, $page, $perPage)
    {
        if (!PackageHelper::isInstalled('bd')) {
            return collect();
        }
        $areaManager = AreaManager::find($areaManagerId);
        if (!$areaManager)  $areaManager = SubAreaManager::with('countries')->find($areaManagerId);
        if (!$areaManager) return collect();

        $countries = $areaManager->countriesQuery()->pluck('id')->toArray();
        return Bd::selectRaw('concat(COALESCE(username, ""), " - ", id) as name, id')
            ->where(function ($query) use ($key) {

                $query->orWhere('id', 'like', '%' . $key . '%');
            })->whereIn('country_id', $countries)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function superAdminUsers($key, $page, $perPage)
    {
        return User::selectRaw('concat(COALESCE(name, ""), " - ", uuid) as name, id')
            ->where(function ($query) {
                $query->where('is_bd', 0)->orWhereNull('is_bd');
            })->where(function ($query) {
                $query->where('is_area_manager', 0)->orWhereNull('is_area_manager');
            })->where(function ($query) {
                $query->where('sub_area_manger', 0)->orWhereNull('sub_area_manger');
            })->where(function ($query) {
                $query->where('is_super_admin', 0)->orWhereNull('is_super_admin');
            })->where(function ($query) {
                $query->where('is_sub_super_admin', 0)->orWhereNull('is_sub_super_admin');
            })
            ->whereDoesntHave('hostAgency', function ($query) {
                $query->where('type', 1);
            })
            ->whereDoesntHave('shippingAgency')
            ->where(function ($query) use ($key) {
                $query->fitterByUuid($key)->orWhere('name', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }
    // public function supSuperAdminUsers($key, $page, $perPage)
    // {
    //     return User::selectRaw('concat(COALESCE(name, ""), " - ", uuid) as name, id')
    //         ->where('is_super_admin', 0)
    //         ->where('is_sub_super_admin', 0)
    //         ->where(function ($query) {
    //             $query->where('agency_id', 0)
    //                 ->orWhereNull('agency_id');
    //         })
    //         ->whereDoesntHave('hostAgency', function ($query) {
    //             $query->where('type', 1);
    //         })
    //         ->whereDoesntHave('shippingAgency')
    //         ->where(function ($query) use ($key) {
    //             $query->fitterByUuid($key)->orWhere('name', 'like', '%' . $key . '%')
    //                 ->orWhere('id', 'like', '%' . $key . '%');
    //         })
    //         ->paginate($perPage, ['*'], 'page', $page);
    // }

    public function supSuperAdminUsers($key, $page, $perPage)
    {
        return User::selectRaw('concat(COALESCE(name, ""), " - ", uuid) as name, id')
            ->where(function ($query) {
                $query->where('is_bd', 0)->orWhereNull('is_bd');
            })->where(function ($query) {
                $query->where('is_area_manager', 0)->orWhereNull('is_area_manager');
            })->where(function ($query) {
                $query->where('sub_area_manger', 0)->orWhereNull('sub_area_manger');
            })->where(function ($query) {
                $query->where('is_super_admin', 0)->orWhereNull('is_super_admin');
            })->where(function ($query) {
                $query->where('is_sub_super_admin', 0)->orWhereNull('is_sub_super_admin');
            })
            ->where(function ($query) {
                $query->where('agency_id', 0)
                    ->orWhereNull('agency_id');
            })
            ->whereDoesntHave('hostAgency', function ($query) {
                $query->where('type', 1);
            })
            ->whereDoesntHave('shippingAgency')
            ->where(function ($query) use ($key) {
                $query->fitterByUuid($key)->orWhere('name', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function subAreaManager($key, $page, $perPage)
    {
        return User::selectRaw('concat(COALESCE(name, ""), " - ", uuid) as name, id')
            ->where(function ($query) {
                $query->where('is_bd', 0)->orWhereNull('is_bd');
            })->where(function ($query) {
                $query->where('is_area_manager', 0)->orWhereNull('is_area_manager');
            })->where(function ($query) {
                $query->where('sub_area_manger', 0)->orWhereNull('sub_area_manger');
            })->where(function ($query) {
                $query->where('is_super_admin', 0)->orWhereNull('is_super_admin');
            })->where(function ($query) {
                $query->where('is_sub_super_admin', 0)->orWhereNull('is_sub_super_admin');
            })
            ->where(function ($query) {
                $query->where('agency_id', 0)
                    ->orWhereNull('agency_id');
            })
            ->whereDoesntHave('hostAgency', function ($query) {
                $query->where('type', 1);
            })
            ->whereDoesntHave('shippingAgency')
            ->where(function ($query) use ($key) {
                $query->fitterByUuid($key)->orWhere('name', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }


    public function superAdminUsers2($key, $page, $perPage)
    {
        return SuperAdmin::selectRaw('concat(COALESCE(username, ""), " - ", id) as name, id')
            ->where(function ($query) use ($key) {
                $query->where('username', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function usersAreaManager($key, $page, $perPage)
    {
        return User::selectRaw('concat(COALESCE(name, ""), " - ", uuid) as name, id')
            ->where(function ($query) {
                $query->where('is_bd', 0)->orWhereNull('is_bd');
            })->where(function ($query) {
                $query->where('is_area_manager', 0)->orWhereNull('is_area_manager');
            })->where(function ($query) {
                $query->where('sub_area_manger', 0)->orWhereNull('sub_area_manger');
            })->where(function ($query) {
                $query->where('is_super_admin', 0)->orWhereNull('is_super_admin');
            })->where(function ($query) {
                $query->where('is_sub_super_admin', 0)->orWhereNull('is_sub_super_admin');
            })
            ->where(function ($query) {
                $query->where('agency_id', 0)
                    ->orWhereNull('agency_id');
            })
            ->whereDoesntHave('hostAgency', function ($query) {
                $query->where('type', 1);
            })
            ->whereDoesntHave('shippingAgency')
            ->where(function ($query) use ($key) {
                $query->fitterByUuid($key)->orWhere('name', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }




    public function searchInAgency($key, $page, $perPage)
    {
        // Safe check - return empty if shipping agency not installed
        $shippingClass = AgencyPackageHelper::getShippingAgencyClass();
        if (!$shippingClass) {
            return collect();
        }

        return $shippingClass::selectRaw('concat(name, " - ", id) as name, id')
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function superAdminAgencies($key, $page, $perPage, $countryId)
    {
        // Safe check - return empty if shipping agency not installed
        $shippingClass = AgencyPackageHelper::getShippingAgencyClass();
        if (!$shippingClass) {
            return collect();
        }

        return $shippingClass::selectRaw('concat(name, " - ", id) as name, id')
            // ->where('country_id', $countryId)
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function usersByCountry($superAdminId, $key, $page, $perPage)
    {
        $superAdmin = SuperAdmin::find($superAdminId);
        if (!$superAdmin) return collect();

        return User::selectRaw('concat(COALESCE(name, ""), " - ", uuid) as name, id')
            ->where(function ($query) {
                $query->where('is_bd', 0)->orWhereNull('is_bd');
            })->where(function ($query) {
                $query->where('is_area_manager', 0)->orWhereNull('is_area_manager');
            })->where(function ($query) {
                $query->where('sub_area_manger', 0)->orWhereNull('sub_area_manger');
            })->where(function ($query) {
                $query->where('is_super_admin', 0)->orWhereNull('is_super_admin');
            })->where(function ($query) {
                $query->where('is_sub_super_admin', 0)->orWhereNull('is_sub_super_admin');
            })
            ->where(function ($query) {
                $query->where('agency_id', 0)
                    ->orWhereNull('agency_id');
            })
            ->whereDoesntHave('hostAgency', function ($query) {
                $query->where('type', 1);
            })
            ->whereDoesntHave('shippingAgency')
            ->where('country_id', $superAdmin->country_id)
            ->where(function ($query) use ($key) {
                $query->fitterByUuid($key)
                    ->orWhere('name', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }


    public function usersByCountries($areaManagerId, $key, $page, $perPage)
    {
        $areaManager = AreaManager::find($areaManagerId);
        if (!$areaManager) $areaManager = SubAreaManager::with('countries')->find($areaManagerId);
        if (!$areaManager) return collect();
        // $countries = $areaManager?->countries?->pluck('id')->toArray() ?? [];
        $countries = $areaManager->countriesQuery()->pluck('id')->toArray();

        return User::selectRaw('concat(COALESCE(name, ""), " - ", uuid) as name, id')
            ->where(function ($query) {
                $query->where('is_bd', 0)->orWhereNull('is_bd');
            })->where(function ($query) {
                $query->where('is_area_manager', 0)->orWhereNull('is_area_manager');
            })->where(function ($query) {
                $query->where('sub_area_manger', 0)->orWhereNull('sub_area_manger');
            })->where(function ($query) {
                $query->where('is_super_admin', 0)->orWhereNull('is_super_admin');
            })->where(function ($query) {
                $query->where('is_sub_super_admin', 0)->orWhereNull('is_sub_super_admin');
            })
            ->where(function ($query) {
                $query->where('agency_id', 0)
                    ->orWhereNull('agency_id');
            })
            ->whereDoesntHave('hostAgency', function ($query) {
                $query->where('type', 1);
            })
            ->whereDoesntHave('shippingAgency')
            ->whereIn('country_id', $countries)
            ->where(function ($query) use ($key) {
                $query->fitterByUuid($key)
                    ->orWhere('name', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function searchInHostAgency($key, $page, $perPage)
    {
        // Safe check - return empty if agency not installed
        $agencyClass = AgencyPackageHelper::getAgencyClass();
        if (!$agencyClass) {
            return collect();
        }

        return $agencyClass::selectRaw('concat(name, " - ", id) as name, id')
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function searchUserAgencyShipping($key, $page, $perPage)
    {
        return User::selectRaw('concat(name, " - ", uuid) as name, id')
            //            ->whereDoesntHave('shippingAgency', function ($query) {
            //                $query->where('type', 2)->where('deleted_at' , null);
            //            })
            ->whereDoesntHave('shippingAgency')
            ->where(function ($query) {
                $query->where('is_bd', 0)->orWhereNull('is_bd');
            })->where(function ($query) {
                $query->where('is_area_manager', 0)->orWhereNull('is_area_manager');
            })->where(function ($query) {
                $query->where('sub_area_manger', 0)->orWhereNull('sub_area_manger');
            })->where(function ($query) {
                $query->where('is_super_admin', 0)->orWhereNull('is_super_admin');
            })->where(function ($query) {
                $query->where('is_sub_super_admin', 0)->orWhereNull('is_sub_super_admin');
            })
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->orWhere('uuid', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%')
                    ->orWhere('special_id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function searchUserFamily($key, $page, $perPage)
    {
        return User::select('id', 'name', 'uuid') // keep light select
            ->where(function ($query) {
                $query->where('family_id', 0)
                    ->orWhereNull('family_id');
            })
            ->where(function ($query) use ($key) {
                $query->fitterByUuid($key)
                    ->orWhere('name', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn($user) => [
                'id'   => $user->id,
                'name' => $user->uuid . ' - ' . $user->name, // accessor used here
            ]);
    }

    public function updateDeviceToken($user, $deviceToken = null)
    {
        if (is_null($user->device_token) || $user->device_token != $deviceToken) {
            $user->device_token = $deviceToken;
            $user->save();
        }
    }

    public function updateOnlineTime(User $user, $currentTime)
    {
        $user->online_time = $currentTime;
        $user->lan = app()->getLocale();
        $user->save();
    }

    public function getUserWithMedals($userId)
    {
        //        $authUserId = auth()->id();
        return User::with([
            'packs',
            //            'packs' => fn($q) => $q->whereIn('type', [4, 5, 6, 25, 13, 18, 15, 20, 10, 12, 17, 28])
            //                ->where(fn($q) => $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp))
            //                ->where('is_used', 1)
            //                ->with(['ware']),
            'UserVip' => fn($q) => $q->with('OVip:id,img'),
            'receiverLevel',
            'senderLevel',
            'chargeLevel',
            'agency' => fn($q) => $q->with(['owner' => fn($q) => $q->select(['id'])->with('profile:id,user_id,avatar')]),
            'profile',
            'ownerRoom' => fn($q) => $q->with('owner.country:id,language'),
            'shippingAgency:id,app_owner_id,name,img',
            //            'chatRoomsAsUser' => function ($q) use ($authUserId) {
            //                $q->where('user_id2', $authUserId)
            //                    ->withCount(['messages as unread_messages_count' => function ($query) use ($authUserId) {
            //                        $query->where('user_id', '<>', $authUserId)
            //                            ->where('status', '<>', 'seen');
            //                    }]);
            //            },
            //            'chatRoomsAsUser2' => function ($q) use ($authUserId) {
            //                $q->where('user_id', $authUserId)
            //                    ->withCount(['messages as unread_messages_count' => function ($query) use ($authUserId) {
            //                        $query->where('user_id', '<>', $authUserId)
            //                            ->where('status', '<>', 'seen');
            //                    }]);
            //            },
        ])
            ->find($userId);
    }

    public function getUsersWithMedals($usersIds)
    {

        return User::with([
            'packs',
            'UserVip' => fn($q) => $q->with('OVip:id,img'),
            'receiverLevel',
            'senderLevel',
            'chargeLevel',
            'agency' => fn($q) => $q->with(['owner' => fn($q) => $q->select(['id'])->with('profile:id,user_id,avatar')]),
            'profile',
            'ownerRoom' => fn($q) => $q->with('owner.country:id,language'),
            'shippingAgency:id,app_owner_id,name,img',

        ])->whereIn('id', $usersIds)->get();
    }

    public function update_user_multi_images($user, $id, $src)
    {
        $updated = ProfileGallary::where('id', $id)
            ->where('user_id', $user->id)
            ->update(['img' => $src]);

        if (!$updated) {
            throw new Exception("Error updating image Or image not found");
        }

        return true;
    }



    public function userCharge()
    {
        return User::where('type_user', 3)->orWhere('type_user', 4)->orderByDesc('id')->paginate(10);
    }

    public function updateLocation($userId, $lat, $long)
    {
        User::whereId($userId)->update([
            "lat"   => $lat,
            "long"  => $long,
        ]);
    }

    public function updateCountry($user, $countryId): void
    {
        $user->update([
            "country_id"   => $countryId,
        ]);
    }

    public function findUserById($id)
    {
        return User::find($id);
    }

    public function findUserByUuid($uuid)
    {
        return User::where('uuid', $uuid)->first();
    }

    public function logProfileVisit($user, $visitorId)
    {
        $user->profileVisits()->syncWithoutDetaching([
            $visitorId => [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function hasLiked($userId, $likedUserId)
    {
        $user = $this->findUserById($userId);
        return $user->likes()->where('liked_user_id', $likedUserId)->exists();
    }


    public function attachLike($userId, $likedUserId)
    {
        $user = $this->findUserById($userId);
        $user->likes()->attach($likedUserId);
    }

    public function detachLike($userId, $likedUserId)
    {
        $user = $this->findUserById($userId);
        $user->likes()->detach($likedUserId);
    }

    public function hasIgnored($userId, $likedUserId)
    {
        $user = $this->findUserById($userId);
        return $user->ignores()->where('ignore_user_id', $likedUserId)->exists();
    }


    public function attachIgnored($userId, $likedUserId)
    {
        $user = $this->findUserById($userId);
        $user->ignores()->attach($likedUserId);
    }

    public function detachIgnored($userId, $likedUserId)
    {
        $user = $this->findUserById($userId);
        $user->ignores()->detach($likedUserId);
    }

    public function decrementBalance(User $user, $amount)
    {
        $user->decrement('di', $amount);
    }

    public function getFollowers($user, $type)
    {
        // Query to get followers based on the type
        return User::whereHas('followers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->paginate(15);
    }

    public function getFolloweds($user)
    {
        return $user->onRoomFolloweds();
    }

    public function getFollowRooms($userId)
    {
        return Follow::query()->whereHas('room', function ($query) {
            $query->withoutAppends()->where('count_room_socket', '!=', 0);
        })->with([
            'room' => function ($query) use ($userId) {
                $query->withoutAppends()->with([
                    'owner' => function ($query) {
                        $query->withoutAppends();
                    }
                ]);
            }
        ])->where('user_id', $userId)->orderByDesc('id')->paginate(10)->pluck('room');
    }

    public function updateUserGame($user, $gameId)
    {
        $user->game_id = $gameId;
        return $user->save();
    }

    public function UsersWithSearch($search)
    {
        return $this->model->query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('id', $search)
                        ->orWhere('uuid', $search);
                });
            })->select('id', 'name', 'uuid')->get();
    }

    public function nearUsers($userId, $latitude, $longitude)
    {
        return  User::query()
            ->with('profile')
            ->select(
                'users.*',
                DB::raw("(6371 * acos(cos(radians($latitude))
                * cos(radians(users.lat))
                * cos(radians(users.long) - radians($longitude))
                + sin(radians($latitude))
                * sin(radians(users.lat)))) AS distance")
            )->whereNotNull('lat')->whereNotNull('long')
            ->whereDoesntHave('ignores', fn($q) => $q->where("ignore_user_id", $userId))
            ->withExists(['likedBy' => fn($q) => $q->where("liked_user_id", $userId)])
            ->where('id', '!=', $userId)->orderBy('distance', 'asc')->paginate(10);
    }

    public function users($userId, $latitude = null, $longitude = null)
    {
        $authUserId = auth()->id();

        $builder = User::query()
            ->with('profile')
            ->whereDoesntHave('ignores', fn($q) => $q->where("ignore_user_id", $userId))
            ->withExists(['likedBy' => fn($q) => $q->where("liked_user_id", $userId)])
            ->where('id', '!=', $userId);
        if (($latitude != null) && ($longitude != null)) {
            $builder = $builder->select(
                'users.*',
                DB::raw("(6371 * acos(cos(radians($latitude))
                    * cos(radians(users.lat))
                    * cos(radians(users.long) - radians($longitude))
                    + sin(radians($latitude))
                    * sin(radians(users.lat)))) AS distance")
            )->whereNotNull('lat')->whereNotNull('long');
        }

        $builder->with([
            'chatRoomsAsUser' => function ($q) use ($authUserId) {
                $q->where('user_id2', $authUserId)
                    ->withCount(['messages as unread_messages_count' => function ($query) use ($authUserId) {
                        $query->where('user_id', '<>', $authUserId)
                            ->where('status', '<>', 'seen');
                    }]);
            },
            'chatRoomsAsUser2' => function ($q) use ($authUserId) {
                $q->where('user_id', $authUserId)
                    ->withCount(['messages as unread_messages_count' => function ($query) use ($authUserId) {
                        $query->where('user_id', '<>', $authUserId)
                            ->where('status', '<>', 'seen');
                    }]);
            },
        ]);

        return $builder->inRandomOrder()->paginate(request('per_page'));
    }


    public function trashedUserAccountList($perPage, $Page, $search, $id)
    {
        return User::onlyTrashed()->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('uuid', $search);
            });
        })->when($id, function ($query) use ($id) {
            $query->where(function ($q) use ($id) {
                $q->where('id', $id);
            });
        })->orderByDesc('deleted_at')->paginate($perPage, ['*'], 'page', $Page);
    }

    public function restoreAccount($id)
    {
        $user = User::query()->onlyTrashed()->find($id);
        $user->restore();
        return true;
    }

    public function softDelete($id)
    {
        $user = User::query()->onlyTrashed()->find($id);
        DB::table('reports')->where('Reporter_id', $user->id)->delete();
        $user->forceDelete();
        return true;
    }

    public function userLevel($perPage, $Page, $search)
    {
        return User::when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('uuid', $search)
                    ->orWhere('phone', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%");
            });
        })->paginate($perPage, ['*'], 'page', $Page);
    }

    public function all($perPage, $Page, $familyId, $agencyId, $search, $host)
    {
        return User::when(isset($familyId), function ($query) use ($familyId) {
            $query->where('family_id', $familyId);
        })->when(isset($agencyId), function ($query) use ($agencyId) {
            $query->where('agency_id', $agencyId);
        })->when(isset($host), function ($query) use ($host) {
            $query->where('is_host', $host);
        })->when(isset($search), function ($query) use ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('uuid', 'like', "%$search%")->orWhere('special_id', 'like', "%$search%")->orWhere('phone', 'like', "%$search%")->orWhere('nickname', 'like', "%$search%")->orWhere('email', 'like', "%$search%");
        })->orderByDesc('id')->with('agency', 'targets')->paginate($perPage, ['*'], 'page', $Page);
    }


    public function findUserData(int $id): Model
    {
        $targetPackTypes = [4, 5, 6, 15, 16, 17, 18, 19, 20, 25];

        $user = User::select([
            'id',
            'uuid',
            'color_id',
            'chat_id',
            'notification_id',
            'name',
            'number_of_fans',
            'number_of_followings',
            'number_of_friends',
            'family_id',
            'total_diamond_received',
            'bio',
            'type_user',
        ])
            ->with([
                'packs' => fn($q) => $q->where('is_used', 1)
                    ->whereIn('type', $targetPackTypes)
                    ->where(fn($q) => $q->where('expire', 0)
                        ->orWhere('expire', '>=', now()->timestamp))
                    ->with('ware'),
                'profile',
                'room.backgroundImage',
                'room.background',
                'room.defaultBackground',
                'family.members',
                'blacklists',
                'chatSetting',
                'userDataSetting',
                'agency',
                'shippingAgency:id,app_owner_id,name,img',
                'specialId.ware',
                'images',
                'manager',
                'Ovip',
                'UserVip' => fn($q) => $q->with('OVip:id,img'),
                'UserVip.Ovip.wares',
                'nowRoomOwner.packs' => fn($q) => $q->where('is_used', 1)->with('ware'),
                'receiverLevel',
                'senderLevel',
                'chargeLevel',
                'agency.owner',
            ])
            ->withCount(['profileVisits as profile_visitors'])
            ->findOrFail($id);


        return $user;
    }



    public function getStats($id)
    {
        $user = User::findOrFail($id);
        return [
            'number_of_fans'       => $user->numberOfFans(),
            'number_of_followings' => $user->numberOfFollowings(),
            'number_of_friends'    => $user->numberOfFriends(),
            'profile_visitors'     => $user->profile_visitors ?? 0,
        ];
    }
    public function getRoomsData($id)
    {
        $user = User::with([
            'room.backgroundImage',
            'room.background',
            'room.defaultBackground',
            'nowRoomOwner.packs' => fn($q) => $q->where('is_used', 1)->with('ware'),
            'agency.owner',
            'agency.members',
            'family.members',
            'shippingAgency.charges'
        ])->find($id);

        if (!$user) {
            return (object)[];
        }


        return [
            'room'            => !$user->getPackWithType(16) ? new UserDataRoomResource($user) : [],
            'now_room'        => UserDataHelper::formatNowRoom($user) ?? [],
            'agency'          => UserDataHelper::formatAgency($user) ?? [],
            'family_id'       => $user->family_id ?? '',
            'shipping_agency' => UserDataHelper::formatShippingAgency($user) ?? [],
            'family_data'     => UserDataHelper::formatFamily($user) ?? [],
        ];
    }

    public function getVipLevelData($id)
    {
        $user = User::with(['UserVip.vip.wares', 'Ovip.wareIcon'])->findOrFail($id);
        return [
            'vip'   => $user->vip_data,
            'level' => $user->level_data,
        ];
    }

    public function getFramesData($id)
    {
        $user = User::with(['UserVip.vip.wares'])->findOrFail($id);
        return [
            'profile_frame'    => $user->profile_frame,
            'profile_frame_id' => $user->profile_frame_id,
        ];
    }

    public function getByParentId(int $parentId)
    {
        return UserEarnInvitation::where('parent_id', $parentId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function claimEarning(int $earningId): ?UserEarnInvitation
    {
        $earning = UserEarnInvitation::find($earningId);
        if (!$earning || $earning->is_claimed) {
            return null;
        }

        $earning->update(['is_claimed' => true]);
        return $earning;
    }
}
