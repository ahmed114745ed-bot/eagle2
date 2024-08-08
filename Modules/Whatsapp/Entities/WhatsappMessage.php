<?php

namespace Modules\Whatsapp\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
}
