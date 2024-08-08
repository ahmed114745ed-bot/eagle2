<?php

namespace Modules\Chat\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatLetter extends Model
{
    use HasFactory;
    protected $table = 'chat_letters';
    protected $guarded = ['id'];
}
