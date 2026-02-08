<?php

namespace Utd\Agency\Facades;

use Illuminate\Support\Facades\Facade;

class ExternalModel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'agency.external.model';
    }
}
