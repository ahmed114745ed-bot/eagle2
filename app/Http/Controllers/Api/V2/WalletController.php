<?php

namespace App\Http\Controllers\Api\V2;

use Carbon\Carbon;
use App\Models\Pack;
use App\Models\Ware;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Tik\Services\MallService;
use Modules\Vip\Entities\UserVip;
use App\Http\Controllers\Controller;
use App\Http\Resources\WareResource;
use App\Http\Resources\WareResourceAll;
use App\Http\Resources\WarePaddingResource;
use App\Http\Resources\BestWareSaleResource;
