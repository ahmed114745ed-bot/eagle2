<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResourceSerche;
use App\Repositories\Community\SearchRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Public\Http\Services\UserCounterServices;

class CommunityController extends Controller
{

    protected $searchRepository;

    public function __construct(SearchRepositoryInterface $searchRepository)
    {
        $this->searchRepository = $searchRepository;
    }

    // search
    public function merge_search(Request $request)
    {
        $keywords = $request->keywords;
        $user_id = $request->user()->id;

        if (!$keywords || !$user_id) {
            return Common::apiResponse(0, 'Missing parameters');
        }

        $this->searchRepository->saveSearchHistory($user_id, $keywords);

        $result = ['user' => UserResourceSerche::collection($this->searchRepository->userSearchHand($user_id, $keywords)), 'rooms' => $this->searchRepository->searchRooms($user_id, (int)$keywords),];

        return Common::apiResponse(1, '', $result, paginationKey: 'user');
    }

    // friends
    public function user_friends()
    {
        $userId = Auth::id();
        $keywords = request()->keywords;
        $perPage = 10;
        $currentPage = request()->has('page') ? request()->page : 1;

        $users = $this->searchRepository->getUserFriends($userId, $keywords, $perPage, $currentPage);
        $countUsers = $users->total();

        return Common::apiResponse(1, 'success', ['user' => UserResourceSerche::collection($users), 'number_of_friends' => $countUsers,]);
    }

    // search history
    public function searchList(Request $request)
    {
        $userId = $request->user()->id;
        $data = $this->searchRepository->getSearchList($userId);

        return Common::apiResponse(1, '', $data);
    }

    //clear search history
    public function cleanSearchList(Request $request)
    {
        $userId = $request->user()->id;
        $result = $this->searchRepository->clearUserSearchHistory($userId);

        if ($result) {
            return Common::apiResponse(1, 'Empty successfully');
        } else {
            return Common::apiResponse(0, 'Empty failed', null, 400);
        }
    }

    // official message
    public function officialMessages(Request $request)
    {
        $userId = $request->user()->id;
        if (!$userId) return Common::apiResponse(0, 'un_auth');

        $page = $request->page ?: 1;
        $data = $this->searchRepository->getOfficialMessages($userId, $page);

        // Update user counters
        (new UserCounterServices)->UpgradeDateForType($request->user(), 'official_message');
        (new UserCounterServices)->UpgradeDateForType($request->user(), 'system_message');

        return Common::apiResponse(1, '', $data);
    }

}
