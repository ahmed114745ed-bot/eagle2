<?php

namespace App\Tik\Services;


use App\Helpers\Common;
use App\Tik\Repositories\GiftRepository;

class GiftService
{

    public function __construct(
        private readonly GiftRepository $giftRepository,
    ) {}

    public function index($type)
    {
        return $this->giftRepository->all($type);
    }

    public function allGift()
    {
        return $this->giftRepository->allGifts();
    }



    public function show($giftId)
    {
        return $this->giftRepository->findByGiftId($giftId);
    }

    public function create($request)
    {
        if ($request->hasFile('img')) {
            $image = Common::upload('images', $request->file('img'));
        }
        if ($request->hasFile('show_img')) {
            $showImg = Common::upload('images', $request->file('show_img'));
        }
        if ($request->hasFile('show_img2')) {
            $showImg2 = Common::upload('images', $request->file('show_img2'));
        }
        $data = [
            'name'         => $request->name,
            'e_name'         => $request->e_name,
            'type'         => $request->type,
            'vip_level'         => $request->vip_level,
            'price'         => $request->price,
            'img'          =>  $image ?? "",
            'show_img'          => $showImg ?? "",
            'image_type'         => $request->image_type,
            'show_img2'          => $showImg2 ?? '',
            'sort'         => $request->sort,
            'enable'         => $request->enable,
            'music_gift'         => $request->music_gift,
        ];

        $gift = $this->giftRepository->create($data);
        if ($request->type == 6) {
            $arrayPercentage = [$request->min_percentage, $request->mid_percentage, $request->max_percentage];
            $luckyGiftData = [
                'win_probability' => $request->win_probability,
                'min_percentage' => implode(', ', $arrayPercentage),
            ];
            $gift->lucky_gift()->attach($luckyGiftData);
        }
        return true;
    }

    public function update($request)
    {
        $data = [
            'name' => $request->name,
            'e_name' => $request->e_name,
            'type' => $request->type,
            'vip_level' => $request->vip_level,
            'price' => $request->price,
            'image_type' => $request->image_type,
            'sort' => $request->sort,
            'enable' => $request->enable,
            'music_gift' => $request->music_gift,
        ];

        if ($request->hasFile('img')) {
            $data['img'] = Common::upload('images', $request->file('img'));
        }

        if ($request->hasFile('show_img')) {
            $data['show_img'] = Common::upload('images', $request->file('show_img'));
        }

        if ($request->hasFile('show_img2')) {
            $data['show_img2'] = Common::upload('images', $request->file('show_img2'));
        }


        $gift = $this->giftRepository->update($data, $request->gift_id);
        if ($request->type == 6) {
            $arrayPercentage = [$request->min_percentage, $request->mid_percentage, $request->max_percentage];
            $luckyGiftData = [
                'win_probability' => $request->win_probability,
                'min_percentage' => implode(', ', $arrayPercentage),
            ];
            $gift->lucky_gift()->sync($luckyGiftData);
        }
        return true;
    }

    public function updateSwitch($requestSwitch, $giftId, $type)
    {
        $gift = $this->giftRepository->giftUpdate($giftId, $type, $requestSwitch);
        return true;
    }
}
