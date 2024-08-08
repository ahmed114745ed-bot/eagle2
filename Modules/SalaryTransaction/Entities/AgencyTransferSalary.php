<?php

namespace Modules\SalaryTransaction\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\SalaryTransaction\Database\factories\AgencyTransferSalaryFactory;

class AgencyTransferSalary extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
   
}
