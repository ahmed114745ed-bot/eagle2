<?php

namespace App\Tik\Repositories;

use App\Models\Gift;
use Illuminate\Support\Facades\Auth;



class GiftRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new Gift());
    }

    public function all($type)
    {
        $user = Auth::user();
     
       
        if ($type == 11 && $user) {
       
            return $user->myGifts()
                ->withPivot('quantity')
                ->where('enable', 1)
                ->orderBy('use_count', 'desc')
                ->orderByRaw('ISNULL(`sort`), `sort`')
                ->orderBy('price')
                ->get();
        }
    
        $query = $this->model->newQuery()->where('enable', 1);
    
        if ($type) {
            $query->where('type', $type);
        }
    
        return $query->orderBy('use_count', 'desc')
            ->orderByRaw('ISNULL(`sort`), `sort`')
            ->orderBy('price')
            ->get();
    }

    public function get_images()
    {
       
            return $this->model->query()
                ->where('enable', 1)
                ->pluck('img'); 
       
    }
    
    public function allGifts($page, $perPage)
    {
        $gifts = $this->model->query()->where('type', '!=', 8)->orderBy("use_count", "desc");

        return $gifts->orderBy('price')->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById($giftId)
    {
        return $this->model->query()->select([
            'id',
            'name',
            'type',
            'price',
            'vip_level',
            'is_play',
            'img',
            'show_img',
            'show_img2',
            'image_type'
        ])->where('id', $giftId)->where('enable', 1)->first();
    }

    public function findByGiftId($giftId)
    {
        return $this->model->query()->with('lucky_gift')->find($giftId);
    }

    public function giftUpdate($giftId, $type, $requestType)
    {
        $this->model->where('id', $giftId)->update([$type => $requestType]);
        return true;
    }

    public function allAchievementGift()
    {
        return $this->model->where('type', 5)->select('id', 'name')->where('enable', true)->get();
    }
}
