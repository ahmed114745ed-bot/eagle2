<?php

namespace Utd\Agency\Facades;

use Illuminate\Support\Facades\Facade;

class ExternalModule extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'agency.external.module';
    }
}
