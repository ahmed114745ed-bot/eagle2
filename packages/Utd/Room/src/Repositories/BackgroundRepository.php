<?php

namespace Utd\Room\Repositories;

use Utd\Room\Entities\Background;

class BackgroundRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new Background());
    }

    public function index()
    {
        return $this->model->where(['enable' => 1])->selectRaw('id,img')->orderBy('use_count', 'desc')->get();
    }

    public function getEnabled()
    {
        return $this->model->where('enable', 1)->get();
    }

    public function getDefault()
    {
        return $this->model->where('enable', 1)->orderBy('id', 'asc')->first();
    }

    /**
     * Get default background image
     */
    public function getDefaultImage()
    {
        return \DB::table('backgrounds')->where('enable', 1)->orderBy('id', 'asc')->limit(1)->first()->img ?? null;
    }
}
