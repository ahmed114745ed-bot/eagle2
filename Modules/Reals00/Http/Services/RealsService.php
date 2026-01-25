<?php

namespace Modules\Reals\Http\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Modules\Reals\Entities\Real;

use Illuminate\Support\Collection;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Modules\Reals\Entities\ReportReals;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\FollowRepository;

define('PAGINATION', 10);
define('REEL_PAGINATION', 10);

class RealsService extends BaseModelService
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    public function getUserReals(User $user, int $currentUserId)
    {
        $userId = $user->id;
        return Real::query()->with([
            'user' => function ($query) use ($currentUserId) {
                $query->withoutAppends()->isFollow($currentUserId)->with('profile');
            }
        ])->withCount(['likes', 'comments'])->withExists([
            'likes' => function ($query) use ($currentUserId) {
                $query->where('user_id', $currentUserId);
            }
        ])->where('user_id', $userId)->orderByDesc('id')->paginate(PAGINATION);
    }

    public function getUserFollowersReals(User $user)
    {
        $userId = $user->id;

        $builder = Real::query()->whereHas('user', function ($query) use ($userId) {
            $query->withoutAppends()->getFollowers($userId);
        });
        if (!request("page")  || request("page") == 1) {
            $user->last_following_reel_id = $builder->latest()->select(['id'])->first()?->id;
        }
        $reels   =  $builder->whereDoesntHave('likes', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with([
            'user' => function ($query) use ($userId) {
                $query->withoutAppends()->isFollow($userId)->with('profile');
            }
        ])->withCount(['likes', 'comments'])->withExists([
            'likes' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }
        ])->where('reals.id', '<=', $user->last_following_reel_id ?? PHP_INT_MAX)->inRandomOrder($user->following_unique_value);

        $countInterested   = $reels->count();
        $currentPage       = request()->page ?? 1;
        $pagination        = REEL_PAGINATION;

        $reels = $reels->paginate($pagination);
        $allData = $reels->items();
        $allData = collect($allData);


        [$_, $allData] =
            $this->getReels($countInterested, [], $userId, $allData, $user->following_unique_value, $user->last_following_reel_id, function ($interestIds, $userId) {
                return $this->getLikedReels($interestIds, $userId, true);
            });

        return $allData;
    }

    public function showNew(User $user, $filter = null)
    {
        $userId        = $user->id;
        $userInterests = $user->interests;

        $interestIds = $userInterests?->pluck('id')?->toArray() ?? [];
        if (!request("page")  || request("page") == 1) {
            $user->last_all_reel_id = Real::select('id')->latest()->first()?->id;
        }
        $reals = Real::query()
   
        ->with([
            'user' => function ($query) use ($userId) {
                $query->withoutAppends()->isFollow($userId)->with('profile');
            }
        ])->withCount(['likes', 'comments'])
        
        ->inRandomOrder($user->real_type);

        if ($filter === 'following') {
            
            $followedUserIds = auth()->user()->friendsFollowedId(); // جلب معرفات الأصدقاء فقط
            $reals->whereIn('user_id', $followedUserIds);
        }

        $countInterested   = $reals->count();
        $currentPage       = request()->page ?? 1;
        $pagination        = 10;

        $reals = $reals->paginate($pagination);


        $allData = $reals->items();
        $allData = collect($allData);

        $maxRealId = $user->last_all_reel_id;

        [$countNotInterest, $allData] =
            $this->getReels($countInterested, $interestIds, $userId, $allData, $user->real_type, $maxRealId, function ($interestIds, $userId) {
                return $this->getNotInterestedReels($interestIds, $userId);
            });


        [$_, $allData] =
            $this->getReels(($countInterested + ($countNotInterest)), $interestIds, $userId, $allData, $user->real_type, $maxRealId, function ($interestIds, $userId) {
                return $this->getLikedReels($interestIds, $userId);
            });


        $paginator = new LengthAwarePaginator($allData, 100, $pagination, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query()
        ]);



        return $reals;
    }
    public function show(User $user): array
    {
        $userId        = $user->id;
        $userInterests = $user->interests;


        $interestIds = $userInterests?->pluck('id')?->toArray() ?? [];
        if (!request("page")  || request("page") == 1) {
            $user->last_all_reel_id = Real::query()->select('id')->latest()->first()?->id;
        }
        $reals = Real::query()->whereHas('categories', function ($query) use ($interestIds) {
            return $query->whereIn('category_id', $interestIds);
        })->with([
            'user' => function ($query) use ($userId) {
                $query->withoutAppends()->isFollow($userId)->with('profile');
            }
        ])->withCount(['likes', 'comments'])
            ->whereDoesntHave('likes', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->where('reals.id', '<=', ($user->last_all_reel_id ?? PHP_INT_MAX))->inRandomOrder($user->real_type);

        $countInterested   = $reals->count();
        $currentPage       = request()->page ?? 1;
        $pagination        = REEL_PAGINATION;

        $reals = $reals->paginate($pagination);
        $allData = $reals->items();
        $allData = collect($allData);

        $maxRealId = $user->last_all_reel_id;

        [$countNotInterest, $allData] =
            $this->getReels($countInterested, $interestIds, $userId, $allData, $user->real_type, $maxRealId, function ($interestIds, $userId) {
                return $this->getNotInterestedReels($interestIds, $userId);
            });


        [$_, $allData] =
            $this->getReels(($countInterested + ($countNotInterest)), $interestIds, $userId, $allData, $user->real_type, $maxRealId, function ($interestIds, $userId) {
                return $this->getLikedReels($interestIds, $userId);
            });


        $paginator = new LengthAwarePaginator($allData, 100, $pagination, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query()
        ]);


        return $paginator->items();
    }

    /**
     * @param $countInterested
     * @param int $perPage
     * @param mixed $currentPage
     * @return array
     */
    public function getNewLimitAndOffset($countInterested, int $perPage, mixed $currentPage): array
    {
        $interestedPageCount                   = (float) $countInterested / $perPage;
        $numOfAdminsPages    = (int)$interestedPageCount;
        $diffWithCurrentPage = $currentPage - $numOfAdminsPages;

        $limit = $perPage;
        if ($diffWithCurrentPage == 1) {
            $limit = $perPage - ($countInterested % $perPage);
            $limit = $limit == 0 ? $perPage : $limit;
        }

        if (($numOfAdminsPages == 0 && $diffWithCurrentPage == 1)) {
            $offset = 0;
        } else if ($countInterested < $perPage && $diffWithCurrentPage == 2) {

            $offset = $perPage - $countInterested;
        } else if (($interestedPageCount - $numOfAdminsPages) > 0.0) {
            $offset = (($currentPage - 1) * $perPage) - (($countInterested) % $perPage) + ($perPage * $numOfAdminsPages);
        } else {
            if ($countInterested == 0) {
                $countInterested = 1;
            }
            $offset = (($currentPage - 1) * $perPage) - (($countInterested) % $perPage) + ($perPage * $numOfAdminsPages);

            //            $offset = $countInterested % $perPage * (($diffWithCurrentPage - 1) * $perPage);
        }

        return [$limit, $offset];
    }

    public function create($data, int $userId)
    {
        $categoriesIds = @$data['categories'];
        $urlVideo = $data['video'];
        unset($data['video']);
        if ($categoriesIds) {
            unset($data['categories']);
        }

        if (isset($data['video']) && is_file($data['video'])) {
            $urlVideo = $this->upload($data['video']);
        } elseif (isset($data['video'])) {
            if (!Storage::exists($data['video'])) {
                return;
            }
        }

        $url               = $urlVideo;
        $data['user_id']   = $userId;
        $data['url']       = $url;
        $data['sub_video'] = $this->makeSubVideo($url, null, 'gcs');
        $real              = Real::query()->create($data);
        // if(is_file($data['video'])) {
            (new FfmpegService())->extract(getDriverUrl() . '/' . $url, $real->id);
        // }
        if ($categoriesIds) {
            $real->categories()->sync($categoriesIds);
        }

        return $real;
    }

    public function oldReal()
    {
        // $reals = Real::chunk(100)->get();
        // foreach( $reals as $real)
        // {
        //     (new FfmpegService())->extract(getDriverUrl().'/'.$real->url,$real->id);
        // }

        Real::chunk(600, function ($reals) {
            foreach ($reals as $real) {
                (new FfmpegService())->extract(getDriverUrl() . '/' . $real->url, $real->id);
            }
        });
    }

    public function makeSubVideo(string $videoPath, ?string $outPutPath, string $storage = 'local'): ?string
    {
        if ($outPutPath == null) {
            $outPutPath = storage_path('app/public/sub-video');
        }

        $outGifName = $outPutPath . DIRECTORY_SEPARATOR . uniqid() . '.gif';
        $videoName  = $outPutPath . DIRECTORY_SEPARATOR . uniqid() . '.mp4';

        $videoPath        = getDriverUrl() . DIRECTORY_SEPARATOR . $videoPath;
        $pythonScriptPath = base_path('/Modules/Reals/Http/Services/script.py');
        $videoPath        = str_replace('\\', '/', $videoPath);


        $path              = null;
        $command           = "python3 $pythonScriptPath $videoPath $videoName $outGifName";
        $output            = shell_exec($command);
        $outGifNameStorage = substr($outGifName, strpos($outGifName, 'public') - 1);
        $videoNameStorage  = substr($videoName, strpos($videoName, 'public') - 1);

        if (Storage::disk('local')->exists($outGifNameStorage)) {
            $path = 'sub-video/' . uniqid() . '.gif';
            Storage::disk($storage)->put($path, file_get_contents($outGifName));

            Storage::disk('local')->delete($outGifNameStorage);
            Storage::disk('local')->delete($videoNameStorage);
        }

        return $path;
    }

    public function showReal(int $realId, $userId)
    {
        return Real::query()->with([
            'user' => function ($query) use ($userId) {
                $query->withoutAppends()->with(['profile'])->isFollow($userId);
            }
        ])->withCount(['likes', 'comments'])->withExists([
            'likes' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }
        ])->where('id', $realId)->first();
    }


    /*
     * $data is = [file, description, categories ids]
     */

    public  static function upload($file): ?string
    {
        $extension      = $file->getClientOriginalExtension();
        $uniqueFileName = Str::random(20) . '_' . uniqid() . '.' . $extension;
        $file->storeAs('videos', $uniqueFileName, 'gcs');
        return 'videos' . DIRECTORY_SEPARATOR . $uniqueFileName;
    }

    public function delete(int|Module $real)
    {
        if (gettype($real) == 'integer') {
            $real = Real::query()->find($real);
        }

        if (auth()->id() != @$real->user_id) {
            return false;
        }


        $real->delete();
        return true;
    }

    /**
     * @param int $countInterested
     * @param int $pagination
     * @param mixed $currentPage
     * @return float|int
     */
    public function getDiffCountWithPage(int $countInterested, int $pagination, mixed $currentPage): int|float
    {
        return $countInterested - ($pagination * $currentPage);
    }

    /**
     * @param array $interestIds
     * @param mixed $userId
     * @return Builder
     */
    public function getNotInterestedReels(array $interestIds, mixed $userId, $lastId = PHP_INT_MAX): Builder
    {
        return Real::query()->whereDoesntHave('categories', function ($query) use ($interestIds) {
            return $query->whereIn('category_id', $interestIds);
        })->with([
            'user' => function ($query) use ($userId) {
                $query->withoutAppends()->isFollow($userId)->with([
                    'profile' => function ($query) {
                        $query->select([
                            'id',
                            'avatar',
                            'user_id',
                        ]);
                    }
                ]);
            }
        ])->withCount(['likes', 'comments'])
            ->whereDoesntHave('likes', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->where('reals.id', '<=', $lastId);
    }

    /**
     * @param array $interestIds
     * @param mixed $userId
     * @return Builder
     */
    public function getLikedReels(array $interestIds, mixed $userId, bool $isFollowing = false): Builder
    {

        $builder = Real::query();
        if ($isFollowing) {
            $builder->whereHas('user', function ($query) use ($userId) {
                $query->withoutAppends()->getFollowers($userId);
            });
        } else {
            $builder->whereHas('user', function ($query) use ($userId) {
                $query->withoutAppends();
            });
        }
        return $builder->with([
            'user' => function ($query) use ($userId) {
                $query->withoutAppends()->isFollow($userId)->with([
                    'profile' => function ($query) {
                        $query->select([
                            'id',
                            'avatar',
                            'user_id',
                        ]);
                    }
                ]);
            }
        ])->withCount(['likes', 'comments'])
            ->withExists([
                'likes' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }
            ])
            ->whereHas('likes', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
    }

    /**
     * @param int $countInterested
     * @param int $pagination
     * @param mixed $currentPage
     * @param array $interestIds
     * @param mixed $userId
     * @param User $user
     * @param Collection $allData
     * @return array
     */
    public function getReels(int $countInterested, array $interestIds, mixed $userId, Collection $allData, string $seed, $lastId, \Closure $closure): array
    {
        if ($lastId === null) $lastId = PHP_INT_MAX;
        $currentPage       = request()->page ?? 1;
        $pagination = REEL_PAGINATION;

        $diffCountWithPage = $this->getDiffCountWithPage($countInterested, $pagination, $currentPage);
        if ($diffCountWithPage < 0) {
            [$limit, $offset] = $this->getNewLimitAndOffset($countInterested, $pagination, $currentPage);

            $anotherData = $closure($interestIds, $userId, $lastId)->where('reals.id', '<=', $lastId)->inRandomOrder($seed);

            $countNotInterest = $anotherData->count();
            $anotherData      = $anotherData->limit($limit)->offset($offset)->get();

            $allData = $allData->merge($anotherData);
        }
        return array($countNotInterest ?? 0, $allData);
    }

    public function deleteReeltAndReport($reelId, $reportId)
    {
        // Find the moment by ID
        $reel = Real::find($reelId);
        if (!$reel) {
            return [
                'success' => false,
                'message' => 'Reel not found',
                'status' => 404,
            ];
        }

        // Find the report moment by ID and delete it
        $reportReel = ReportReals::find($reportId);
        if ($reportReel) $reportReel->delete();

        // Delete the moment
        $reel->delete();

        return [
            'success' => true,
            'message' => 'Reel and report successfully deleted',
            'status' => 200,
        ];
    }

    public function update( $reel_id, array $data)
    {
        $reel = Real::find($reel_id);

        if (!$reel) {
            throw new \Exception('Reel not found');
        }

        $categoriesIds = $data['categories'] ?? null;
        $updateData = [];

       
        if (isset($data['video']) && is_file($data['video'])) {
            $urlVideo = $this->upload($data['video']);
            $updateData['url'] = $urlVideo;
            $updateData['sub_video'] = $this->makeSubVideo($urlVideo, null, 'gcs');
        
            
        } 


        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }

     
        if (!empty($updateData)) {
            $reel->update($updateData);
        }

        if ($categoriesIds) {
            $reel->categories()->sync($categoriesIds);
        }

        return $reel;
      
    }

    
}
