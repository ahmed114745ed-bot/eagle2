<?php

namespace Modules\Reals\Http\Controllers\web;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Modules\Reals\Entities\Real;
use App\Admin\Controllers\MainController;

class AdminReelController extends MainController
{
    public function index(Content $content)
    {
        $reels = Real::with(['user.profile', 'user.country'])
            ->withCount(['likes', 'comments', 'Views'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($reel) {
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
                    'gifts_count' => 0,
                    'created_at' => $reel->created_at,
                ];
            });

        return $content
            ->body(view('reals::admin.reels.index', compact('reels')));
    }
    
    public function loadMore(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 20);
        
        $reels = Real::with(['user.profile', 'user.country'])
            ->withCount(['likes', 'comments', 'Views'])
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($reel) {
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
                    'gifts_count' => 0,
                    'created_at' => $reel->created_at,
                ];
            });
        
        \Log::info('Load More Reels', [
            'offset' => $offset,
            'limit' => $limit,
            'returned' => $reels->count(),
            'total' => Real::count()
        ]);
            
        return response()->json(['reels' => $reels]);
    }

    public function show($id, Content $content)
    {
        $reel = Real::with(['user.profile', 'user.country', 'likes.user.profile', 'likes.user.country', 'comments.user.profile', 'comments.user.country'])
            ->withCount(['likes', 'comments', 'Views'])
            ->findOrFail($id);

        $videoUrl = $this->buildMediaUrl($reel->url);
        $thumbnailUrl = $this->buildMediaUrl($reel->intro_image);

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
                'gifts_count' => 0,
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
        // الهدايا غير مفعلة حالياً في النظام
        return response()->json(['gifts' => []]);
    }
    
    public function batchCounts(Request $request)
    {
        try {
            $reelIds = $request->input('reel_ids', []);
            
            if (empty($reelIds) || !is_array($reelIds)) {
                return response()->json(['reels' => []]);
            }
            
            // Limit to prevent abuse
            $reelIds = array_slice($reelIds, 0, 10);
            
            $reels = Real::whereIn('id', $reelIds)
                ->withCount(['likes', 'comments', 'Views'])
                ->get(['id'])
                ->map(function($reel) {
                    return [
                        'id' => $reel->id,
                        'likes_count' => $reel->likes_count ?? 0,
                        'comments_count' => $reel->comments_count ?? 0,
                        'views_count' => $reel->views_count ?? 0,
                        'gifts_count' => 0,
                    ];
                });
            
            return response()->json(['reels' => $reels]);
        } catch (\Exception $e) {
            \Log::error('Error fetching batch counts', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['reels' => []], 500);
        }
    }
    
    public function update($id, \Illuminate\Http\Request $request = null)
    {
        $request = $request ?? request();
        
        try {
            $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);
            
            $reel = Real::findOrFail($id);
            
            // تحديث الوصف (description هو العمود المستخدم في الريلز)
            if ($request->has('description')) {
                $reel->description = $request->description;
            }
            
            // في حالة وجود title, نضعه في description أيضاً
            if ($request->has('title') && !$request->has('description')) {
                $reel->description = $request->title;
            }
            
            $reel->save();
            
            \Log::info('Reel Updated', [
                'reel_id' => $id,
                'description' => $reel->description,
                'user_id' => $reel->user_id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الريل بنجاح',
                'reel' => [
                    'id' => $reel->id,
                    'title' => $reel->description ?: 'بدون عنوان',
                    'description' => $reel->description,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating reel', [
                'reel_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $reel = Real::findOrFail($id);
            
            \Log::info('Deleting Reel', [
                'reel_id' => $id,
                'user_id' => $reel->user_id,
                'description' => $reel->description
            ]);
            
            // Delete related records
            $reel->likes()->delete();
            $reel->comments()->delete();
            $reel->Views()->delete();
            
            // Delete the reel
            $reel->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الريل بنجاح'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting reel', [
                'reel_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()
            ], 500);
        }
    }

    private function buildMediaUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Full URL already provided
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Try the default disk URL first
        $base = getDriverUrl();
        if ($base) {
            return rtrim($base, '/') . '/' . ltrim($path, '/');
        }

        // Fallback to explicit GCS disk URL if default disk has no URL configured (e.g., FILESYSTEM_DISK=local)
        $gcsBase = config('filesystems.disks.gcs.url');
        if ($gcsBase) {
            return rtrim($gcsBase, '/') . '/' . ltrim($path, '/');
        }

        // Fallback to storage path
        return asset('storage/' . ltrim($path, '/'));
    }
}
