<?php

namespace App\Tik\Repositories;

use App\Models\Gift;



class GiftRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new Gift());
    }

    public function all($request)
    {
        $gifts = $this->model->query()->where('enable', 1)->orderBy("use_count", "desc");
        if ($request->type) {
            $gifts = $gifts->where('type', $request->type);
        }
        return $gifts->orderByRaw('ISNULL(`sort`), `sort`')->orderBy('price')->get();
    }

    public function allGifts()
    {
        $gifts = $this->model->query()->where('type', '!=', 8)->orderBy("use_count", "desc");
        
        return $gifts->orderByRaw('ISNULL(`sort`), `sort`')->orderBy('price')->get();

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

    public function giftUpdate($giftId,$type,$requestType)
    {
        $this->model->where('id',$giftId)->update([$type=>$requestType]);
        return true;
    }

    
}
