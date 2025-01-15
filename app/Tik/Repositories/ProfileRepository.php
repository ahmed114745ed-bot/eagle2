<?php

namespace App\Tik\Repositories;

use App\Models\ProfileVisitor;

class ProfileRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new ProfileVisitor());
    }

    
}