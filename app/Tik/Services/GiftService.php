<?php

namespace App\Tik\Services;


use App\Tik\Repositories\GiftRepository;

class GiftService
{

    public function __construct(
        private readonly GiftRepository $giftRepository,
    ) {}

    public function index($request)
    {
        return $this->giftRepository->all($request);
    }

    public function show($giftId)
    {
        return $this->giftRepository->findByGiftId($giftId);
    }

    public function create($request)
    {
        $data = [
            'name'         => $request->name,
            'e_name'         => $request->e_name,
            'type'         => $request->type,
            'vip_level'         => $request->vip_level,
            'price'         => $request->price,
            'img'          => $request->img,
            'show_img'          => $request->show_img,
            'image_type'         => $request->image_type,
            'show_img2'          => $request->show_img2,
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
            'name'         => $request->name,
            'e_name'         => $request->e_name,
            'type'         => $request->type,
            'vip_level'         => $request->vip_level,
            'price'         => $request->price,
            'img'          => $request->img,
            'show_img'          => $request->show_img,
            'image_type'         => $request->image_type,
            'show_img2'          => $request->show_img2,
            'sort'         => $request->sort,
            'enable'         => $request->enable,
            'music_gift'         => $request->music_gift,
        ];

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
}
