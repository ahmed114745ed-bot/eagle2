<?php

namespace Modules\Vip\Services;

use App\Models\Ware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Reals\Http\Services\FfmpegService;
use Modules\Public\Http\Services\UserCounterServices;

class WareSaveService
{
    public function handle($form)
    {
        $this->ensureUniqueCombination($form);

        $isEditing = $form->isEditing();
        $hasShowImg = $form->show_img || $form->model()->show_img;
        $hasImg2 = $form->img2 || $form->model()->img2;

        if (!$hasShowImg && !$hasImg2) {
            throw ValidationException::withMessages([
                'show_img' => ['Please upload at least one image.'],
            ]);
        }

        $imageType1 = $this->handleShowImg($form);
        $profileFrameType = $this->handleImg2($form);

        $final = $profileFrameType ?? $imageType1;

        if ($form->type != 18 && $form->type != 21 && $isEditing && is_null($final)) {
            session()->flash('show_alert', 'الرجاء اختيار نوع الصورة');
            redirect()->back()->send();
        }

        if ($final) {
            $form->model()->image_type = $final;
        }

        (new UserCounterServices)->eventUsers('ware');
    }

    protected function ensureUniqueCombination($form)
    {
        $id = $form->model()->id;

        $exists = Ware::where('level', $form->model()->level)
            ->where('type', $form->type)
            ->where('get_type', 1)
            ->when($id, function ($query) use ($id) {
                $query->where('id', '!=', $id);
            })
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'type' => [__('This level and type combination already exists')],
            ]);
        }
    }

    protected function handleShowImg($form)
    {
        if ($form->show_img instanceof UploadedFile) {
            $allowedExtensions = ['svga', 'mp4', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg', 'webp', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm'];
            $ext = strtolower($form->show_img->guessExtension());

            if (!in_array($ext, $allowedExtensions)) {
                throw ValidationException::withMessages([
                    'show_img' => ['Invalid file type. Allowed extensions are: ' . implode(', ', $allowedExtensions)],
                ]);
            }

            return $ext;
        }

        return null;
    }

    protected function handleImg2($form)
    {
        if ($form->img2 instanceof UploadedFile) {
            $allowedExtensions = ['svga', 'mp4', 'alpha', 'vap', 'png'];
            $ext = strtolower($form->img2->guessExtension());
            $originalExt = strtolower($form->img2->getClientOriginalExtension());

            if ($ext === 'zz' && $originalExt === 'svga') $ext = 'svga';
            if ($ext === 'gif' && $originalExt === 'gif') $ext = 'png';

            if ($ext === 'mp4') {
                $urlVideo = upload($form->img2);
                $videoPath = getDriverUrl() . '/' . $urlVideo;
                $wareId = $form->model()->id;

                (new FfmpegService())->extractByDuration($videoPath, $wareId);

                $imagePath = (config('app.env') != 'production' ? '' : 'test-') . "frames/{$wareId}.jpg";
                $response = Http::attach(
                    'image',
                    Storage::disk('gcs')->get($imagePath),
                    "{$wareId}.jpg"
                )->post('https://utd-test.utdsoftware.com/api/analyze-media');

                $data = $response->json();
                if ($response->successful() && isset($data['data']['video_type'])) {
                    $ext = strtolower($data['data']['video_type']);
                }
            }

            if (!in_array($ext, $allowedExtensions)) {
                throw ValidationException::withMessages([
                    'img2' => ['Invalid file type. Allowed extensions are: ' . implode(', ', $allowedExtensions)],
                ]);
            }

            $form->input('detected_profile_frame_type', $ext);
            $form->profile_frame_type = $ext;

            return $ext;
        }

        return null;
    }
}
