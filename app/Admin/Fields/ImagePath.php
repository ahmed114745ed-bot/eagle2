<?php

namespace App\Admin\Fields;

use App\Tik\Services\Files\ImageConverter;
use Encore\Admin\Form\Field\ImageField;

class ImagePath extends Image
{

    public function prepare($image)
    {
        if ($this->picker) {
            return parent::prepare($image);
        }

        if (request()->has(static::FILE_DELETE_FLAG)) {
            return $this->destroy();
        }

        $this->name = $this->getStoreName($image);

        $path = ImageConverter::toWebpAndUpload($image, 'banners');

//        $this->callInterventionMethods($image->getRealPath());

//        $path = $this->uploadAndDeleteOriginal($image);

        $this->uploadAndDeleteOriginalThumbnail($image);

        return $path;
    }

}
