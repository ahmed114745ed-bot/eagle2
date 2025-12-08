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

    public function getByCategory(?int $categoryId = null, ?int $type = null, int $perPage = 10)
    {
        $user = Auth::user();

        $query = ($type === -1 && $user)
            ? $user->myGifts()->withPivot('quantity')
            : $this->model->newQuery()->where('enable', 1);

        if ($categoryId && $type !== -1) {
            $query->where('gift_category_id', $categoryId);
        }

        return $query->orderBy('use_count', 'desc')
                    ->orderByRaw('ISNULL(`sort`), `sort`')
                    ->orderBy('price')
                    ->paginate($perPage);
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
