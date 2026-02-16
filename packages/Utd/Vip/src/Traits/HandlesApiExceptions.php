<?php

namespace Utd\Vip\Traits;

use App\Helpers\Common;
use Exception;

trait HandlesApiExceptions
{
    public function wrap(callable $callback)
    {
        try {
            return $callback();
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 400);
        }
    }
}
