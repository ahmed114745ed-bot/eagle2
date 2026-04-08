<?php

namespace Utd\Reals\Http\Controllers\Web;

use App\Admin\Controllers\MainController;
use Encore\Admin\Layout\Content;
use Exception;
use Illuminate\Http\Request;
use Log;
use Utd\Gifts\Entities\GiftLog;
use Utd\Reals\Entities\Real;

class AdminReelController extends MainController
{
    public function index(Content $content)
    {
        // Generate or retrieve random seed for this session
        if (! session()->has('reels_random_seed')) {
            session(['reels_random_seed' => mt_rand(1, 999999)]);
        }
        $seed = session('reels_random_seed');

        $reels = Real::with(['user.profile', 'user.country'])
            ->withCount(['likes', 'comments', 'Views'])
            ->orderByRaw('RAND(?)', [$seed])
            ->limit(15)
            ->get();

        // Get gifts counts if available
        $giftsCounts = $this->getGiftsCountsForReels($reels->pluck('id')->toArray());

        $reels = $reels->map(function ($reel) use ($giftsCounts) {
            $videoUrl = $this->buildMediaUrl($reel->url);
            $thumbnailUrl = $this->buildMediaUrl($reel->intro_image);

            return [
                'id' => $reel->id,
                'user_id' => $reel->user_id,
                'user' => $reel->user,
                'title' => $reel->description ?: 'بدون عنوان',
                'description' => $reel->description,
                'video_url' => $videoUrl,
                'thumbnail_url' => $thumbnailUrl ?: null,
                'thumbnail_needs_capture' => empty($thumbnailUrl),
                'likes_count' => $reel->likes_count ?? 0,
                'comments_count' => $reel->comments_count ?? 0,
                'views_count' => $reel->views_count ?? 0,
                'gifts_count' => $giftsCounts[$reel->id] ?? 0,
                'created_at' => $reel->created_at,
            ];
        });

        return $content
            ->body(view('reals::admin.reels.index', compact('reels', 'seed')));
    }

    public function loadMore(Request $request)
    {
        $limit = $request->input('limit', 20);
        $excludeIds = $request->input('exclude_ids', []);
        $seed = $request->input('seed', session('reels_random_seed', mt_rand(1, 999999)));

        // Store seed in session if not exists
        if (! session()->has('reels_random_seed')) {
            session(['reels_random_seed' => $seed]);
        }

        $query = Real::with(['user.profile', 'user.country'])
            ->withCount(['likes', 'comments', 'Views']);

        if (! empty($excludeIds) && is_array($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        $reels = $query->orderByRaw('RAND(?)', [$seed])
            ->take($limit)
            ->get();

        // Get gifts counts if available
        $giftsCounts = $this->getGiftsCountsForReels($reels->pluck('id')->toArray());

        $reels = $reels->map(function ($reel) use ($giftsCounts) {
            $videoUrl = $this->buildMediaUrl($reel->url);
            $thumbnailUrl = $this->buildMediaUrl($reel->intro_image);

            return [
                'id' => $reel->id,
                'user_id' => $reel->user_id,
                'user' => $reel->user,
                'title' => $reel->description ?: 'بدون عنوان',
                'description' => $reel->description,
                'video_url' => $videoUrl,
                'thumbnail_url' => $thumbnailUrl ?: null,
                'thumbnail_needs_capture' => empty($thumbnailUrl),
                'likes_count' => $reel->likes_count ?? 0,
                'comments_count' => $reel->comments_count ?? 0,
                'views_count' => $reel->views_count ?? 0,
                'gifts_count' => $giftsCounts[$reel->id] ?? 0,
                'created_at' => $reel->created_at,
            ];
        });

        return response()->json([
            'reels' => $reels,
            'seed' => $seed,
            'has_more' => Real::whereNotIn('id', array_merge($excludeIds, $reels->pluck('id')->toArray()))->exists(),
        ]);
    }

    public function show($id, ?Content $content = null)
    {
        $reel = Real::with(['user.profile', 'user.country', 'likes.user.profile', 'likes.user.country', 'comments.user.profile', 'comments.user.country'])
            ->withCount(['likes', 'comments', 'Views'])
            ->findOrFail($id);

        $videoUrl = $this->buildMediaUrl($reel->url);
        $thumbnailUrl = $this->buildMediaUrl($reel->intro_image);

        // Get gifts count for this reel
        $giftsCounts = $this->getGiftsCountsForReels([$id]);

        return response()->json([
            'reel' => [
                'id' => $reel->id,
                'user_id' => $reel->user_id,
                'user' => $reel->user,
                'title' => $reel->description ?: 'بدون عنوان',
                'description' => $reel->description,
                'video_url' => $videoUrl,
                'thumbnail_url' => $thumbnailUrl ?: null,
                'thumbnail_needs_capture' => empty($thumbnailUrl),
                'likes_count' => $reel->likes_count ?? 0,
                'comments_count' => $reel->comments_count ?? 0,
                'views_count' => $reel->views_count ?? 0,
                'gifts_count' => $giftsCounts[$id] ?? 0,
                'created_at' => $reel->created_at,
            ],
            'likes' => $reel->likes,
            'comments' => $reel->comments,
            'gifts' => [],
        ]);
    }

    public function getLikes($id)
    {
        $reel = Real::findOrFail($id);
        $likes = $reel->likes()->with(['user.profile', 'user.country'])->get();

        return response()->json(['likes' => $likes]);
    }

    public function getComments($id)
    {
        $reel = Real::findOrFail($id);
        $comments = $reel->comments()->with(['user.profile', 'user.country'])->orderBy('created_at', 'desc')->get();

        return response()->json(['comments' => $comments]);
    }

    public function getGifts($id)
    {
        try {
            // Check if GiftLog class exists (from Gifts package)
            if (! class_exists(GiftLog::class)) {
                return response()->json(['gifts' => []]);
            }

            $GiftLogClass = GiftLog::class;

            // Get gifts for this reel from gift_logs where type='reel'
            $gifts = $GiftLogClass::where('real_id', $id)
                ->with(['sender.profile', 'sender.country', 'gift'])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($giftLog) {
                    return [
                        'id' => $giftLog->id,
                        'user' => $giftLog->sender ? [
                            'id' => $giftLog->sender->id,
                            'name' => $giftLog->sender->name,
                            'profile' => $giftLog->sender->profile,
                            'country' => $giftLog->sender->country,
                        ] : null,
                        'gift' => $giftLog->gift ? [
                            'id' => $giftLog->gift->id,
                            'name' => $giftLog->gift->name,
                            'icon' => $giftLog->gift->icon,
                            'diamond' => $giftLog->gift->diamond,
                        ] : null,
                        'count' => $giftLog->count ?? 1,
                        'created_at' => $giftLog->created_at,
                    ];
                });

            return response()->json(['gifts' => $gifts]);
        } catch (Exception $e) {
            // If any error occurs, return empty array
            Log::error('Error loading gifts for reel: '.$e->getMessage());

            return response()->json(['gifts' => []]);
        }
    }

    public function batchCounts(Request $request)
    {
        try {
            $reelIds = $request->input('reel_ids', []);

            if (empty($reelIds) || ! is_array($reelIds)) {
                return response()->json(['reels' => []]);
            }

            $reelIds = array_slice($reelIds, 0, 10);

            // Get gifts counts if GiftLog exists
            $giftsCounts = [];
            if (class_exists(GiftLog::class)) {
                try {
                    $GiftLogClass = GiftLog::class;
                    $giftsCountsQuery = $GiftLogClass::selectRaw('real_id, COUNT(*) as count')
                        ->whereIn('real_id', $reelIds)
                        ->groupBy('real_id')
                        ->get();

                    foreach ($giftsCountsQuery as $item) {
                        $giftsCounts[$item->real_id] = $item->count;
                    }
                } catch (Exception $e) {
                    Log::warning('Could not load gift counts: '.$e->getMessage());
                }
            }

            $reels = Real::whereIn('id', $reelIds)
                ->withCount(['likes', 'comments', 'Views'])
                ->get(['id'])
                ->map(function ($reel) use ($giftsCounts) {
                    return [
                        'id' => $reel->id,
                        'likes_count' => $reel->likes_count ?? 0,
                        'comments_count' => $reel->comments_count ?? 0,
                        'views_count' => $reel->views_count ?? 0,
                        'gifts_count' => $giftsCounts[$reel->id] ?? 0,
                    ];
                });

            return response()->json(['reels' => $reels]);
        } catch (Exception $e) {
            Log::error('Error fetching batch counts', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['reels' => []], 500);
        }
    }

    public function update($id, ?Request $request = null)
    {
        $request ??= request();

        try {
            $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $reel = Real::findOrFail($id);

            if ($request->has('description')) {
                $reel->description = $request->description;
            }

            if ($request->has('title') && ! $request->has('description')) {
                $reel->description = $request->title;
            }

            $reel->save();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الريل بنجاح',
                'reel' => [
                    'id' => $reel->id,
                    'title' => $reel->description ?: 'بدون عنوان',
                    'description' => $reel->description,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Error updating reel', [
                'reel_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $reel = Real::findOrFail($id);

            $reel->likes()->delete();
            $reel->comments()->delete();
            $reel->Views()->delete();

            $reel->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الريل بنجاح',
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting reel', [
                'reel_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف: '.$e->getMessage(),
            ], 500);
        }
    }

    private function buildMediaUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $envBase = env('MEDIA_BASE_URL');
        if ($envBase) {
            return rtrim($envBase, '/').'/'.ltrim($path, '/');
        }

        $base = getDriverUrl();
        if ($base) {
            return rtrim($base, '/').'/'.ltrim($path, '/');
        }

        $gcsBase = config('filesystems.disks.gcs.url');
        if ($gcsBase) {
            return rtrim($gcsBase, '/').'/'.ltrim($path, '/');
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    /**
     * Helper method to get gifts counts for multiple reels safely
     * Returns empty array if GiftLog class doesn't exist
     */
    private function getGiftsCountsForReels(array $reelIds): array
    {
        if (empty($reelIds)) {
            return [];
        }

        // Check if GiftLog class exists (from Gifts package)
        if (! class_exists(GiftLog::class)) {
            return [];
        }

        try {
            $GiftLogClass = GiftLog::class;
            $giftsCountsQuery = $GiftLogClass::selectRaw('real_id, COUNT(*) as count')
                ->whereIn('real_id', $reelIds)
                ->whereNotNull('real_id')
                ->groupBy('real_id')
                ->get();

            $giftsCounts = [];
            foreach ($giftsCountsQuery as $item) {
                $giftsCounts[$item->real_id] = $item->count;
            }

            return $giftsCounts;
        } catch (Exception $e) {
            Log::warning('Could not load gift counts for reels: '.$e->getMessage());

            return [];
        }
    }
}
