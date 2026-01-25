<?php

namespace Utd\Reals\Services;

use App\Contracts\RealsContract;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Utd\Reals\Entities\Real;
use Utd\Reals\Entities\RealUserComment;
use Utd\Reals\Entities\RealUserLike;
use Utd\Reals\Entities\RealUserView;
use Utd\Reals\Entities\ReportReals;
use Utd\Reals\Services\BaseModelService;
use Utd\Reals\Services\FfmpegService;

define('PAGINATION', 10);
define('REEL_PAGINATION', 10);


class RealsService extends BaseModelService implements RealsContract
{
    public function __construct(Model $model = null)
    {
        parent::__construct($model ?? new Real());
    }

   
    public function getUserReals(int|User $userId, int $currentUserId = null, int $perPage = 10): LengthAwarePaginator
    {
        if ($userId instanceof User) {
            $user = $userId;
            $userId = $user->id;
        }
        
        if ($currentUserId === null) {
            $currentUserId = auth()->id();
        }
        
        return Real::query()
            ->with([
                'user' => function ($query) use ($currentUserId) {
                    $query->withoutAppends()->isFollow($currentUserId)->with('profile');
                }
            ])
            ->withCount(['likes', 'comments'])
            ->withExists([
                'likes' => function ($query) use ($currentUserId) {
                    $query->where('user_id', $currentUserId);
                }
            ])
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

   
    public function getFollowingReals(int $userId, int $perPage = 10): Collection|LengthAwarePaginator
    {
        return $this->getUserFollowersReals(User::find($userId));
    }

    public function getUserFollowersReals(User $user)
    {
        $userId = $user->id;

        $builder = Real::query()->whereHas('user', function ($query) use ($userId) {
            $query->withoutAppends()->getFollowers($userId);
        });
        
        if (!request("page") || request("page") == 1) {
            $user->last_following_reel_id = $builder->latest()->select(['id'])->first()?->id;
        }
        
        $reels = $builder->whereDoesntHave('likes', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with([
            'user' => function ($query) use ($userId) {
                $query->withoutAppends()->isFollow($userId)->with('profile');
            }
        ])->withCount(['likes', 'comments'])->withExists([
            'likes' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }
        ])->where('reals.id', '<=', $user->last_following_reel_id ?? PHP_INT_MAX)
            ->inRandomOrder($user->following_unique_value);

        $countInterested = $reels->count();
        $pagination = REEL_PAGINATION;

        $reels = $reels->paginate($pagination);
        $allData = collect($reels->items());

        [$_, $allData] = $this->getReels($countInterested, [], $userId, $allData, $user->following_unique_value, $user->last_following_reel_id, function ($interestIds, $userId) {
            return $this->getLikedReels($interestIds, $userId, true);
        });

        return $allData;
    }

  
    public function getAllReals(int $userId, ?string $filter = null, int $perPage = 10): LengthAwarePaginator
    {
        $user = User::find($userId);
        return $this->showNew($user, $filter);
    }

    public function showNew(User $user, $filter = null)
    {
        $userId = $user->id;
        $userInterests = $user->interests;

        $interestIds = $userInterests?->pluck('id')?->toArray() ?? [];
        if (!request("page") || request("page") == 1) {
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
            $followedUserIds = auth()->user()->friendsFollowedId();
            $reals->whereIn('user_id', $followedUserIds);
        }

        $countInterested = $reals->count();
        $pagination = 10;

        $reals = $reals->paginate($pagination);
        $allData = collect($reals->items());

        $maxRealId = $user->last_all_reel_id;

        [$countNotInterest, $allData] = $this->getReels($countInterested, $interestIds, $userId, $allData, $user->real_type, $maxRealId, function ($interestIds, $userId) {
            return $this->getNotInterestedReels($interestIds, $userId);
        });

        [$_, $allData] = $this->getReels(($countInterested + ($countNotInterest)), $interestIds, $userId, $allData, $user->real_type, $maxRealId, function ($interestIds, $userId) {
            return $this->getLikedReels($interestIds, $userId);
        });

        return $reals;
    }

    public function show(User $user): array
    {
        $userId = $user->id;
        $userInterests = $user->interests;

        $interestIds = $userInterests?->pluck('id')?->toArray() ?? [];
        if (!request("page") || request("page") == 1) {
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
            })->where('reals.id', '<=', ($user->last_all_reel_id ?? PHP_INT_MAX))
            ->inRandomOrder($user->real_type);

        $countInterested = $reals->count();
        $pagination = REEL_PAGINATION;

        $reals = $reals->paginate($pagination);
        $allData = collect($reals->items());

        $maxRealId = $user->last_all_reel_id;

        [$countNotInterest, $allData] = $this->getReels($countInterested, $interestIds, $userId, $allData, $user->real_type, $maxRealId, function ($interestIds, $userId) {
            return $this->getNotInterestedReels($interestIds, $userId);
        });

        [$_, $allData] = $this->getReels(($countInterested + ($countNotInterest)), $interestIds, $userId, $allData, $user->real_type, $maxRealId, function ($interestIds, $userId) {
            return $this->getLikedReels($interestIds, $userId);
        });

        $paginator = new LengthAwarePaginator($allData, 100, $pagination, request()->page ?? 1, [
            'path' => request()->url(),
            'query' => request()->query()
        ]);

        return $paginator->items();
    }

 
    public function getRealById(int $realId, int $currentUserId): ?array
    {
        $real = $this->showReal($realId, $currentUserId);

        if (!$real) {
            return null;
        }

        return [
            'id' => $real->id,
            'user_id' => $real->user_id,
            'description' => $real->description,
            'video_url' => $real->url,
            'thumbnail' => $real->thumbnail,
            'likes_count' => $real->likes_count,
            'comments_count' => $real->comments_count,
            'views_count' => $real->views_count ?? 0,
            'is_liked' => $real->likes_exists ?? false,
            'user' => [
                'id' => $real->user->id,
                'name' => $real->user->name,
                'avatar' => $real->user->profile?->avatar,
            ],
            'created_at' => $real->created_at,
        ];
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


    public function createReal(int $userId, array $data): ?array
    {
        $real = $this->create($data, $userId);
        return $this->getRealById($real->id, $userId);
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
                return null;
            }
        }

        $url = $urlVideo;
        $data['user_id'] = $userId;
        $data['url'] = $url;
        $data['sub_video'] = $this->makeSubVideo($url, null, 'gcs');
        $real = Real::query()->create($data);
        
        if (class_exists(FfmpegService::class)) {
            (new FfmpegService())->extract(getDriverUrl() . '/' . $url, $real->id);
        }
        
        if ($categoriesIds) {
            $real->categories()->sync($categoriesIds);
        }

        return $real;
    }

    public function updateReal(int $realId, array $data): bool
    {
        $real = $this->update($realId, $data);
        return $real !== null;
    }

    public function update($reel_id, array $data)
    {
        $reel = Real::find($reel_id);

        if (!$reel) {
            return null;
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

 
    public function deleteReal(int $realId): bool
    {
        return $this->delete($realId);
    }

    public function delete(int $real): bool
    {
        $realModel = Real::query()->find($real);

        if (!$realModel) {
            return false;
        }

        if (auth()->id() != @$realModel->user_id) {
            return false;
        }

        $realModel->delete();
        return true;
    }


    public function toggleLike(int $realId, int $userId): array
    {
        $existingLike = RealUserLike::where('real_id', $realId)
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            RealUserLike::create([
                'real_id' => $realId,
                'user_id' => $userId,
            ]);
            $liked = true;
        }

        $likesCount = RealUserLike::where('real_id', $realId)->count();

        return [
            'success' => true,
            'message' => $liked ? 'Liked' : 'Unliked',
            'liked' => $liked,
            'likes_count' => $likesCount,
        ];
    }

    public function addComment(int $realId, int $userId, string $content): ?array
    {
        $comment = RealUserComment::create([
            'real_id' => $realId,
            'user_id' => $userId,
            'comment' => $content,
        ]);

        $comment->load('user.profile');

        return [
            'id' => $comment->id,
            'content' => $comment->comment,
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'avatar' => $comment->user->profile?->avatar,
            ],
            'created_at' => $comment->created_at,
        ];
    }


    public function deleteComment(int $commentId, int $userId): bool
    {
        return RealUserComment::where('id', $commentId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

  
    public function getComments(int $realId, int $perPage = 20): LengthAwarePaginator
    {
        return RealUserComment::where('real_id', $realId)
            ->with('user.profile')
            ->orderByDesc('id')
            ->paginate($perPage);
    }


    public function getLikes(int $realId, int $perPage = 20): LengthAwarePaginator
    {
        return RealUserLike::where('real_id', $realId)
            ->with('user.profile')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

 
    public function recordView(int $realId, int $userId): void
    {
        RealUserView::firstOrCreate([
            'real_id' => $realId,
            'user_id' => $userId,
        ]);
    }

    public function getUserRealsCount(int $userId): int
    {
        return Real::where('user_id', $userId)->count();
    }


    public function reportReal(int $realId, int $userId, string $reason): bool
    {
        ReportReals::create([
            'real_id' => $realId,
            'Reporter_id' => $userId,
            'Reported_id' => Real::find($realId)?->user_id,
            'description' => $reason,
        ]);

        $reportsCount = ReportReals::where('real_id', $realId)->count();
        $threshold = config('reals.reports.auto_hide_threshold', 5);

        if ($reportsCount >= $threshold) {
            Real::where('id', $realId)->update(['is_hidden' => true]);
        }

        return true;
    }

    public function isFeatureAvailable(): bool
    {
        return true;
    }

    
    public function getNewLimitAndOffset($countInterested, int $perPage, mixed $currentPage): array
    {
        $interestedPageCount = (float) $countInterested / $perPage;
        $numOfAdminsPages = (int) $interestedPageCount;
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
        }

        return [$limit, $offset];
    }

    public static function upload($file): ?string
    {
        $extension = $file->getClientOriginalExtension();
        $uniqueFileName = Str::random(20) . '_' . uniqid() . '.' . $extension;
        $file->storeAs('videos', $uniqueFileName, 'gcs');
        return 'videos' . DIRECTORY_SEPARATOR . $uniqueFileName;
    }

    public function makeSubVideo(string $videoPath, ?string $outPutPath, string $storage = 'local'): ?string
    {
        if ($outPutPath == null) {
            $outPutPath = storage_path('app/public/sub-video');
        }

        $outGifName = $outPutPath . DIRECTORY_SEPARATOR . uniqid() . '.gif';
        $videoName = $outPutPath . DIRECTORY_SEPARATOR . uniqid() . '.mp4';

        $videoPath = getDriverUrl() . DIRECTORY_SEPARATOR . $videoPath;
        $pythonScriptPath = base_path('/packages/Utd/Reals/src/Http/Services/script.py');
        $videoPath = str_replace('\\', '/', $videoPath);

        $path = null;
        $command = "python3 $pythonScriptPath $videoPath $videoName $outGifName";
        $output = shell_exec($command);
        $outGifNameStorage = substr($outGifName, strpos($outGifName, 'public') - 1);
        $videoNameStorage = substr($videoName, strpos($videoName, 'public') - 1);

        if (Storage::disk('local')->exists($outGifNameStorage)) {
            $path = 'sub-video/' . uniqid() . '.gif';
            Storage::disk($storage)->put($path, file_get_contents($outGifName));

            Storage::disk('local')->delete($outGifNameStorage);
            Storage::disk('local')->delete($videoNameStorage);
        }

        return $path;
    }

    public function getDiffCountWithPage(int $countInterested, int $pagination, mixed $currentPage): int|float
    {
        return $countInterested - ($pagination * $currentPage);
    }

    public function getNotInterestedReels(array $interestIds, mixed $userId, $lastId = PHP_INT_MAX): Builder
    {
        return Real::query()->whereDoesntHave('categories', function ($query) use ($interestIds) {
            return $query->whereIn('category_id', $interestIds);
        })->with([
            'user' => function ($query) use ($userId) {
                $query->withoutAppends()->isFollow($userId)->with([
                    'profile' => function ($query) {
                        $query->select(['id', 'avatar', 'user_id']);
                    }
                ]);
            }
        ])->withCount(['likes', 'comments'])
            ->whereDoesntHave('likes', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->where('reals.id', '<=', $lastId);
    }

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
                        $query->select(['id', 'avatar', 'user_id']);
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

    public function getReels(int $countInterested, array $interestIds, mixed $userId, Collection $allData, string $seed, $lastId, \Closure $closure): array
    {
        if ($lastId === null) $lastId = PHP_INT_MAX;
        $currentPage = request()->page ?? 1;
        $pagination = REEL_PAGINATION;

        $diffCountWithPage = $this->getDiffCountWithPage($countInterested, $pagination, $currentPage);
        $countNotInterest = 0;
        
        if ($diffCountWithPage < 0) {
            [$limit, $offset] = $this->getNewLimitAndOffset($countInterested, $pagination, $currentPage);

            $anotherData = $closure($interestIds, $userId, $lastId)->where('reals.id', '<=', $lastId)->inRandomOrder($seed);

            $countNotInterest = $anotherData->count();
            $anotherData = $anotherData->limit($limit)->offset($offset)->get();

            $allData = $allData->merge($anotherData);
        }
        return array($countNotInterest, $allData);
    }

    public function deleteReeltAndReport($reelId, $reportId)
    {
        $reel = Real::find($reelId);
        if (!$reel) {
            return [
                'success' => false,
                'message' => 'Reel not found',
                'status' => 404,
            ];
        }

        $reportReel = ReportReals::find($reportId);
        if ($reportReel) $reportReel->delete();

        $reel->delete();

        return [
            'success' => true,
            'message' => 'Reel and report successfully deleted',
            'status' => 200,
        ];
    }

    public function oldReal()
    {
        Real::chunk(600, function ($reals) {
            foreach ($reals as $real) {
                if (class_exists(FfmpegService::class)) {
                    (new FfmpegService())->extract(getDriverUrl() . '/' . $real->url, $real->id);
                }
            }
        });
    }
}
