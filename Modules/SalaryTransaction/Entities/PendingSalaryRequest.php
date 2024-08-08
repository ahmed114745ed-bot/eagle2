<?php

namespace Modules\SalaryTransaction\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\SalaryTransaction\Database\factories\PendingSalaryRequestFactory;

class PendingSalaryRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
