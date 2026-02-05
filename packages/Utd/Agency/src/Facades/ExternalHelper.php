<?php

namespace Utd\Agency\Facades;

use Illuminate\Support\Facades\Facade;

class ExternalHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'agency.external.helper';
    }
}
