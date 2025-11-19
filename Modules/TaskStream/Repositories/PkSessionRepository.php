<?php

namespace Modules\TaskStream\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Modules\TaskStream\Entities\PkSession;

class PkSessionRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new PkSession());
    }
}
