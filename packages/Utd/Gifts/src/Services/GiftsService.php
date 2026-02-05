<?php

namespace Utd\Gifts\Services;

use App\Contracts\GiftsContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Utd\Gifts\Entities\Gift;
use Utd\Gifts\Entities\GiftCategory;
use Utd\Gifts\Entities\GiftLog;
use Utd\Gifts\Entities\GiftRanking;
use Utd\Gifts\Support\ModelResolver;

/**
 * GiftsService
 * 
 */
class GiftsService implements GiftsContract
{
    /**
     * 
     * @param int $userId
     * @return Collection
     */
    public function getUserGifts($userId)
    {
        $userModel = ModelResolver::getUserModel();
        
        if (!$userModel) {
            return collect();
        }
        
        $user = $userModel::find($userId);
        
        if (!$user) {
            return collect();
        }

        return $user->belongsToMany(Gift::class, 'user_gifts')
            ->withPivot('quantity', 'expire')
            ->withTimestamps()
            ->get();
    }

    /**
     * 
     * @param int $senderId
     * @param int $receiverId
     * @param int $giftId
     * @param int $quantity
     * @param array $options
     * @return mixed
     */
    public function sendGift($senderId, $receiverId, $giftId, $quantity = 1, array $options = [])
    {
      
        
        $gift = Gift::find($giftId);
        
        if (!$gift) {
            return ['success' => false, 'message' => 'Gift not found'];
        }

        if (!$this->canSendGift($senderId, $giftId, $quantity)) {
            return ['success' => false, 'message' => 'Cannot send gift'];
        }

        $giftLog = GiftLog::create(array_merge([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'giftId' => $giftId,
            'giftNum' => $quantity,
            'giftPrice' => $gift->price * $quantity,
            'giftName' => $gift->name,
        ], $options));

        return ['success' => true, 'data' => $giftLog];
    }

    /**
     * 
     * @param int|null $categoryId
     * @return Collection
     */
    public function getGiftsByCategory($categoryId = null)
    {
        $query = Gift::with('category', 'luckyGift');

        if ($categoryId) {
            $query->where('gift_category_id', $categoryId);
        }

        return $query->where('enable', 1)
            ->orderBy('sort')
            ->get();
    }

    /**
     * 
     * @param array $filters
     * @return mixed
     */
    public function getGiftLogs(array $filters = [])
    {
        $query = GiftLog::with(['gift', 'sender', 'receiver']);

        // Apply filters
        if (!empty($filters['sender_id'])) {
            $query->where('sender_id', $filters['sender_id']);
        }

        if (!empty($filters['receiver_id'])) {
            $query->where('receiver_id', $filters['receiver_id']);
        }

        if (!empty($filters['gift_id'])) {
            $query->where('giftId', $filters['gift_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 20);
    }

    /**
     * 
     * @param int $giftId
     * @return mixed
     */
    public function getGift($giftId)
    {
        return Gift::with(['category', 'luckyGift', 'vip'])->find($giftId);
    }

    /**
     * 
     * @return Collection
     */
    public function getAllGifts()
    {
        return Cache::remember('gifts_all', config('gifts.cache.ttl', 3600), function () {
            return Gift::with(['category', 'luckyGift'])
                ->where('enable', 1)
                ->orderBy('sort')
                ->get();
        });
    }

    /**
     * الحصول على فئات الهدايا
     * 
     * @return Collection
     */
    public function getGiftCategories()
    {
        return Cache::remember('gift_categories', config('gifts.cache.ttl', 3600), function () {
            return GiftCategory::with('gifts')
                ->orderBy('sort')
                ->get();
        });
    }

    /**
     * 
     * @param int $userId
     * @param int $giftId
     * @param int $quantity
     * @return bool
     */
    public function canSendGift($userId, $giftId, $quantity = 1)
    {
        $userModel = ModelResolver::getUserModel();
        
        if (!$userModel) {
            return false;
        }
        
        $user = $userModel::find($userId);
        $gift = Gift::find($giftId);

        if (!$user || !$gift) {
            return false;
        }

        $totalPrice = $gift->price * $quantity;
        if ($user->coins < $totalPrice) {
            return false;
        }

        $maxQuantity = config('gifts.max_gift_quantity', 9999);
        if ($quantity > $maxQuantity) {
            return false;
        }

        if ($gift->vip_level > 0) {
            $vipTrait = ModelResolver::getTrait('vip_level');
            
            if ($vipTrait && in_array($vipTrait, class_uses($user))) {
                if ($user->vip_level < $gift->vip_level) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * الحصول على ترتيب الهدايا
     * 
     * @param string $type
     * @param array $filters
     * @return mixed
     */
    public function getGiftRankings($type, array $filters = [])
    {
        $query = GiftRanking::with('ranker')->where('type', $type);

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        return $query->orderBy('total_gifts', 'desc')
            ->limit($filters['limit'] ?? 100)
            ->get();
    }

    /**
     * 
     * @param array $data
     * @return mixed
     */
    public function createGiftLog(array $data)
    {
        return GiftLog::create($data);
    }
}
