<?php

namespace Utd\Family\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait DashBoardTrait
{
    public function store_img(UploadedFile $file, $folder = null)
    {
        $name = Str::random(25);

        return $file->storeAs(
            $folder,
            $name.'.'.$file->getClientOriginalExtension(),
            $this->getStorageDisk()
        );
    }

    public function delete_img($path = null)
    {
        if (! $path) {
            return;
        }

        Storage::disk($this->getStorageDisk())->delete($path);
    }

    public function user_type($type)
    {
        $types = [
            0 => 'User',
            1 => 'Host',
            2 => 'Host Agent',
            3 => 'Shipping Agent',
            4 => 'Host and Shipping Agent',
            5 => 'Administrator',
        ];

        return $types[$type] ?? null;
    }

    public function ware_types($type)
    {
        $types = [
            1 => 'Gemstone',
            3 => 'Card Scroll',
            4 => 'Avatar Frame',
            5 => 'Bubble Frame',
            6 => 'Entering Special Effects',
            7 => 'Microphone Aperture',
            8 => 'Badge',
            9 => 'NoKick',
            10 => 'Icon',
            11 => 'intro animation',
            12 => 'wapel',
            13 => 'hide country',
            14 => 'vip gifts',
            15 => 'no pan',
            16 => 'hidden room',
            17 => 'anonymous man',
            18 => 'colored name',
            19 => 'profile visitors hide in',
            20 => 'hide last active',
        ];

        return $types[$type] ?? null;
    }

    public function wares_main_type($type)
    {
        $types = [
            4 => 'purchase',
            6 => 'vip wares',
        ];

        return $types[$type] ?? null;
    }

    public function vip_previlage_type($type)
    {
        $types = [
            1 => 'Gemstone',
            3 => 'Card Scroll',
            4 => 'Avatar Frame',
            5 => 'Bubble Frame',
            6 => 'Entering Special Effects',
            7 => 'Microphone Aperture',
            8 => 'Badge',
            9 => 'NoKick',
            10 => 'Icon',
            11 => 'intro animation',
            12 => 'wapel',
            13 => 'hide country',
            14 => 'vip gifts',
            15 => 'no pan',
            16 => 'hidden room',
            17 => 'anonymous man',
            18 => 'colored name',
            19 => 'profile visitors hide in',
            20 => 'last login',
        ];

        return $types[$type] ?? null;
    }

    public function cuarsel_type($type)
    {
        $types = [
            0 => 'normal',
            1 => 'Room',
            2 => 'url',
        ];

        return $types[$type] ?? null;
    }

    public function cuarsel_from_type($type)
    {
        $types = [
            0 => '',
            1 => 'hours',
            2 => 'days',
            3 => 'month',
        ];

        return $types[$type] ?? null;
    }

    public function store_music(UploadedFile $file, $folder)
    {
        $name = Str::random(25);

        return $file->storeAs(
            $folder,
            $name.'.'.$file->getClientOriginalExtension(),
            $this->getStorageDisk()
        );
    }

    protected function getStorageDisk(): string
    {
        return config('family.dashboard.storage_disk', 'gcs');
    }
}
